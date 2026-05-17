<?php

namespace WeblaborMx\Front\Imports;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;
use WeblaborMx\Front\Facades\Front;
use WeblaborMx\Front\Jobs\FrontStore;

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
        $fields = $this->indexFront()->importableIndexFields($this->columns);
        $originalRequest = request();

        foreach ($rows as $rowIndex => $row) {
            $data = [];

            foreach ($fields as $field) {
                $heading = str($field->title)->slug('_')->toString();

                if (! $row->has($heading)) {
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

            if (count($data) === 0) {
                $this->ignored++;

                continue;
            }

            try {
                $storeFront = $this->storeFront();
                $rowRequest = $this->requestForRow($originalRequest, $data);
                app()->instance('request', $rowRequest);

                $response = $storeFront->beforeRequest();
                if ($response) {
                    $this->addError($rowIndex, __('The row could not be imported.'));

                    continue;
                }

                $response = (new FrontStore($rowRequest, $storeFront))->handle();
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

    private function storeFront()
    {
        return Front::makeResource($this->resource)->setSource('store');
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
        $rowRequest->setMethod('POST');
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
