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
use WeblaborMx\Front\Jobs\FrontStore;
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
            $id = $row[$idHeading] ?? null;
            $object = filled($id) ? $indexFront->excelObjectForKey($id) : null;
            if (filled($id) && is_null($object)) {
                $this->addError($rowIndex, __('front::messages.import_id_not_found'));

                continue;
            }

            try {
                $data = [];

                foreach ($fields as $field) {
                    $heading = $indexFront->excelHeadingForField($field);

                    if (!$row->has($heading)) {
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
                $data = $indexFront->importData($data, $object, $row);
            } catch (ValidationException $exception) {
                $this->addError($rowIndex, collect($exception->errors())->flatten()->implode(' '));

                continue;
            } catch (Throwable $throwable) {
                report($throwable);
                $this->addError($rowIndex, __('The row could not be imported.'));

                continue;
            }

            if (!is_array($data) || count($data) === 0) {
                $this->ignored++;

                continue;
            }

            try {
                $rowRequest = $this->requestForRow($originalRequest, $data, is_null($object) ? 'POST' : 'PUT');
                app()->instance('request', $rowRequest);

                $response = is_null($object)
                    ? $this->storeRow($rowRequest)
                    : $this->updateRow($rowRequest, $object);

                if ($response) {
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

    private function createFront()
    {
        return Front::makeResource($this->resource)->setSource('store');
    }

    private function updateFront($object)
    {
        return Front::makeResource($this->resource)->setSource('update')->setObject($object);
    }

    private function storeRow(Request $request)
    {
        $front = $this->createFront();
        if (!$this->hasAction($front, 'create') || !$this->hasAction($front, 'store')) {
            return true;
        }

        Gate::authorize('create', $front->getModel());

        $response = $front->beforeRequest();
        if ($response) {
            return $response;
        }

        $response = (new FrontStore($request, $front))->handle();

        return isResponse($response);
    }

    private function updateRow(Request $request, $object)
    {
        $front = $this->updateFront($object);
        if (!$this->hasAction($front, 'edit') || !$this->hasAction($front, 'update')) {
            return true;
        }

        Gate::authorize('update', $object);

        $response = $front->beforeRequest();
        if ($response) {
            return $response;
        }

        $response = (new FrontUpdate($request, $front, $object))->handle();

        return isResponse($response);
    }

    private function hasAction($front, string $method): bool
    {
        return in_array($method, $front->actions);
    }

    private function requestForRow(Request $originalRequest, array $data, string $method): Request
    {
        $rowRequest = $originalRequest->duplicate(
            $originalRequest->query->all(),
            $data,
            $originalRequest->attributes->all(),
            $originalRequest->cookies->all(),
            [],
            $originalRequest->server->all(),
        );
        $rowRequest->setMethod($method);
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
