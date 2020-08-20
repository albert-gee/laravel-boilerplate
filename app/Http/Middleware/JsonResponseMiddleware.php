<?php

namespace App\Http\Middleware;

use App\Http\Models\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse as BaseJsonResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class JsonResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $originalContent = $response->getOriginalContent();

        if ($originalContent instanceof JsonResponse) {
            $responseData = $originalContent;
        } else {
            if ($originalContent instanceof ResourceCollection) {
                $data = $this->getResourceCollectionData($originalContent);
            } else if ($originalContent instanceof Collection) {
                $data = $this->getCollectionData($originalContent);
            } else if ($originalContent instanceof JsonResource || $originalContent instanceof Model) {
                $data = [
                    "id" => $response->getOriginalContent()->id,
                    "type" => $response->getOriginalContent()->getTable(),
                    "attributes" => $this->getData($response)
                ];
            } else {
                $data = [];
            }

            $responseData = new JsonResponse($response->getStatusCode(), $data, [], $this->getMeta($response));
        }


        $statusCode = $response->getStatusCode();

        return response()->json($responseData, $statusCode);
    }

    private function getCollectionData(Collection $collection): array
    {
        $data = [];
        foreach ($collection as $model) {
            array_push($data, [
                "id" => $model->id,
                "type" => $model->getTable(),
                "attributes" => $model
            ]);
        }
        return $data;
    }

    private function getResourceCollectionData(ResourceCollection $collection): array
    {
        $data = [];
        foreach ($collection->jsonSerialize()['data'] as $resource) {
            array_push($data, [
                "id" => $resource->id,
                "type" => $resource->getTable(),
                "attributes" => $resource
            ]);
        }

        return $data;
    }

    private function getData(BaseJsonResponse $response): array {

        if ($response->getOriginalContent() instanceof ResourceCollection) {
            $data = $response->getOriginalContent()->getCollection();
        } else {
            $data = $response->getOriginalContent();
        }

        return (is_array($data)) ? $data : $data->jsonSerialize();
    }


    private function getMeta(BaseJsonResponse $response): array {
        $meta = [];

        if ($response->getOriginalContent() instanceof ResourceCollection) {
            $paginationData = $response->getOriginalContent()->resource->toArray();
            $meta = [
                "current_page" => $paginationData["current_page"],
                "first_page_url" => $paginationData['first_page_url'],
                "from" => $paginationData['from'],
                "last_page" => $paginationData["last_page"],
                "last_page_url" => $paginationData['last_page_url'],
                "next_page_url" => $paginationData['next_page_url'],
                "path" => $paginationData['path'],
                "per_page" => $paginationData['per_page'],
                "prev_page_url" => $paginationData['prev_page_url'],
                "to" => $paginationData['from'],
                "total" => $paginationData['total'],
            ];
        }

        $meta["statusCode"] = $response->status();
        return $meta;
    }
}
