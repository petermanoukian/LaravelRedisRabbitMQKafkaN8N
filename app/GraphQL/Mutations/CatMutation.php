<?php

namespace App\GraphQL\Mutations;
use App\Events\CatAdded;
use App\Events\CatUpdated;
use App\Services\CatService;
use App\Http\Requests\CatRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CatMutation
{
    protected CatService $cats;

    public function __construct(CatService $cats)
    {
        $this->cats = $cats;
    }


    public function createCat($root, array $args)
    {
        // Safely build files array
        $files = [];
        if (isset($args['file']) && $args['file']) {
            $files['filer'] = $args['file'];
        }

        // Build a Request object
        $request = Request::create('/', 'POST', [
            'name' => $args['name'] ?? null,
            'des'  => $args['des'] ?? null,
            'dess' => $args['dess'] ?? null,
        ], [], $files);

        // Run validation using CatRequest rules
        $validator = Validator::make(
            $request->all() + $request->files->all(),
            (new \App\Http\Requests\CatRequest())->rules(),
            (new \App\Http\Requests\CatRequest())->messages()
        );

        $validator->validate();
        $data = $validator->validated();
        $folder       = 'uploads/cats/file';
        $baseFileName = $data['name'] ?? 'cat';
        //return $this->cats->create($data, $request, $folder, $baseFileName);
        $cat = $this->cats->create($data, $request, $folder, $baseFileName);
        event(new CatAdded($cat));
        return $cat;     
    }


    public function updateCat($root, array $args)
    {
        $files = [];

        if (! empty($args['file'])) {
            $files['filer'] = $args['file'];
        }

        // 1️⃣ Create the request
        $request = Request::create(
            '/',
            'POST',
            [
                'id'   => $args['id'],
                'name' => $args['name'] ?? null,
                'des'  => $args['des'] ?? null,
                'dess' => $args['dess'] ?? null,
            ],
            [],
            $files
        );

        // 2️⃣ Bind request to FormRequest
        $formRequest = CatRequest::createFrom($request);
        $formRequest->setContainer(app())->setRedirector(app('redirect'));

        // 3️⃣ Validate
        $validated = validator(
            $formRequest->all() + $formRequest->files->all(),
            $formRequest->rules(),
            $formRequest->messages()
        )->validate();

        $folder       = 'uploads/cats/file';
        $baseFileName = $validated['name'] ?? 'cat';

        $id = (int) $args['id'];

        $catupdated = $this->cats->update(
                $id,
                $validated,
                $request,
                $folder,
                $baseFileName
            );

            // 5️⃣ Fire the event
            event(new CatUpdated($catupdated));

            return $catupdated;
    }


    public function deleteCat($root, array $args): bool
    {
        $id = (int) $args['id'];

        $catx = $this->cats->findById($id);
        if (!$catx) {
            return false;
        }

        // Call the service delete method
        $this->cats->delete($id);
        event(new \App\Events\CatDeleted($id, $catx->name, $catx->des));
        return true;
    }

    public function deleteCats($root, array $args): bool
    {
        $ids = $args['ids'] ?? [];

        if (empty($ids)) {
            return false;
        }

        // Loop through each cat to capture details before deletion
        foreach ($ids as $id) {
            $catx = $this->cats->findById($id);
            if ($catx) {
                $this->cats->delete($id);
                event(new \App\Events\CatDeleted($id, $catx->name, $catx->des));
            }
        }

        return true;
    }



}
