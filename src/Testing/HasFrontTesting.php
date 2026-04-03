<?php

namespace WeblaborMx\Front\Testing;

use WeblaborMx\Front\Resource;

trait HasFrontTesting
{
    public function assertFrontResource(array $params)
    {
        /** @var class-string<Resource> $resourceClass */
        $resourceClass = $params['resource'];
        $user = $params['user'];

        $resource = new $resourceClass();
        $baseUrl = $resource->base_url;
        $modelClass = $resource->getModel();

        $this->actingAs($user);

        $this->get($baseUrl)->assertOk();
        $this->get("{$baseUrl}/create")->assertOk();

        $model = $params['model'] ?? $modelClass::factory()->create();
        $this->get("{$baseUrl}/{$model->getKey()}/edit")->assertOk();
    }
}
