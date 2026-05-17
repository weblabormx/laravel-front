<?php

namespace WeblaborMx\Front\Imports;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;
use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Jobs\FrontUpdate;

class FrontResourceImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    public int $ignored = 0;

    public array $errors = [];

    public function __construct(
        private string $resource,
        private array $columns,
    ) {}

    public function collection(Collection $rows): void
    {
        $indexFront = $this->indexFront();
        $fields = $indexFront->importableIndexFields($this->columns);
        $idHeading = $indexFront->excelIdHeadingKey();
        $originalRequest = request();

        foreach ($rows as $rowIndex => $row) {
            $object = $indexFront->excelObjectForKey($row[$idHeading] ?? null);
            if (is_null($object)) {
                $this->ignored++;

                continue;
            }

            $data = [];

            foreach ($fields as $field) {
                $heading = $indexFront->excelHeadingForField($field);

                if (!$row->has($heading)) {
                    $this->ignored++;

                    continue;
                }

                if (is_callable($field->import_callback)) {
                    $callback = $field->import_callback;
                    $data = $callback($data, $row[$heading], $field, $row);

                    continue;
                }

                $data[$field->column] = $field->parseExcelValue($row[$heading]);
            }

            $data = $indexFront->processExcel($data, 'import', $row, $object);

            if (count($data) === 0) {
                $this->ignored++;

                continue;
            }

            try {
                $updateFront = $this->updateFront($object);
                Gate::authorize('update', $object);
                $rowRequest = $this->requestForRow($originalRequest, $data);
                app()->instance('request', $rowRequest);

                $response = $updateFront->beforeRequest();
                if ($response) {
                    $this->addError($rowIndex, __('The row could not be imported.'));

                    continue;
                }

                $response = (new FrontUpdate($rowRequest, $updateFront, $object))->handle();
                if (isResponse($response)) {
                    $this->addError($rowIndex, __('The row could not be imported.'));

                    continue;
                }

                $this->imported++;
            } catch (ValidationException $exception) {
                $this->addError($rowIndex, collect($exception->errors())->flatten()->implode(' '));
            } catch (Throwable $throwable) {
                report($throwable);
                $this->addError($rowIndex, __('The row could not be imported.'));
            } finally {
                app()->instance('request', $originalRequest);
            }
        }
    }

    private function indexFront()
    {
        return Front::makeResource($this->resource)->setSource('index');
    }

    private function updateFront($object)
    {
        return Front::makeResource($this->resource)->setSource('update')->setObject($object);
    }

    private function requestForRow(Request $originalRequest, array $data): Request
    {
        $rowRequest = $originalRequest->duplicate(
            $originalRequest->query->all(),
            $data,
            $originalRequest->attributes->all(),
            $originalRequest->cookies->all(),
            [],
            $originalRequest->server->all(),
        );
        $rowRequest->setMethod('PUT');
        $rowRequest->setUserResolver($originalRequest->getUserResolver());
        $rowRequest->setRouteResolver($originalRequest->getRouteResolver());

        return $rowRequest;
    }

    private function addError(int $rowIndex, string $message): void
    {
        $this->errors[] = [
            'row' => $rowIndex + 2,
            'message' => $message,
        ];
    }
}
