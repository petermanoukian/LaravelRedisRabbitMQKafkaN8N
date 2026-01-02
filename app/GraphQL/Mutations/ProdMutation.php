<?php

namespace App\GraphQL\Mutations;

use App\Events\Prod\ProdAdded;
use App\Events\Prod\ProdUpdated;
use App\Events\Prod\ProdDeleted;
use App\Services\ProdService;
use App\Http\Requests\ProdRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdMutation
{
    protected ProdService $prods;

    public function __construct(ProdService $prods)
    {
        $this->prods = $prods;
    }

    public function createProd($root, array $args)
    {
        $files = [];
        if (!empty($args['img'])) {
            $files['img'] = $args['img'];
        }
        if (!empty($args['img2'])) {
            $files['img2'] = $args['img2'];
        }

        // Build a Request object
        $request = Request::create('/', 'POST', [
            'catid'     => $args['catid'] ?? null,
            'name'      => $args['name'] ?? null,
            'des'       => $args['des'] ?? null,
            'dess'      => $args['dess'] ?? null,
            'filer'     => $args['filer'] ?? null,
            'filename'  => $args['filename'] ?? null,
            'mime'      => $args['mime'] ?? null,
            'sizer'     => $args['sizer'] ?? null,
            'extension' => $args['extension'] ?? null,
        ], [], $files);

        // Bind request to FormRequest
        $formRequest = ProdRequest::createFrom($request);
        $formRequest->setContainer(app())->setRedirector(app('redirect'));

        // Validate
        $validated = validator(
            $formRequest->all() + $formRequest->files->all(),
            $formRequest->rules(),
            $formRequest->messages()
        )->validate();

        $fileFolder   = 'uploads/prods/file';
        $imageFolder  = 'uploads/prods/img';
        $thumbFolder  = 'uploads/prods/img/thumb';
        $baseFileName = $validated['name'] ?? 'prod';

        $prod = $this->prods->create(
            $validated,
            $formRequest,
            null,   // FileUploadRequest
            null,   // ImageUploadRequest
            $fileFolder,
            $imageFolder,
            $thumbFolder,
            $baseFileName
        );

        event(new ProdAdded($prod));
        return $prod;
    }

    public function updateProd($root, array $args)
    {
        $files = [];
        if (!empty($args['img'])) {
            $files['img'] = $args['img'];
        }
        if (!empty($args['img2'])) {
            $files['img2'] = $args['img2'];
        }

        $request = Request::create('/', 'POST', [
            'id'        => $args['id'],
            'catid'     => $args['catid'] ?? null,
            'name'      => $args['name'] ?? null,
            'des'       => $args['des'] ?? null,
            'dess'      => $args['dess'] ?? null,
            'filer'     => $args['filer'] ?? null,
            'filename'  => $args['filename'] ?? null,
            'mime'      => $args['mime'] ?? null,
            'sizer'     => $args['sizer'] ?? null,
            'extension' => $args['extension'] ?? null,
        ], [], $files);

        $formRequest = ProdRequest::createFrom($request);
        $formRequest->setContainer(app())->setRedirector(app('redirect'));

        $validated = validator(
            $formRequest->all() + $formRequest->files->all(),
            $formRequest->rules(),
            $formRequest->messages()
        )->validate();

        $fileFolder   = 'uploads/prods/file';
        $imageFolder  = 'uploads/prods/img';
        $thumbFolder  = 'uploads/prods/img/thumb';
        $baseFileName = $validated['name'] ?? 'prod';

        $id = (int) $args['id'];

        $produpdated = $this->prods->update(
            $id,
            $validated,
            $formRequest,
            null,   // FileUploadRequest
            null,   // ImageUploadRequest
            $fileFolder,
            $imageFolder,
            $thumbFolder,
            $baseFileName
        );

        event(new ProdUpdated($produpdated));
        return $produpdated;
    }


    public function deleteProd($root, array $args): bool
    {
        $id = (int) $args['id'];

        $prodx = $this->prods->findById($id);
        if (!$prodx) {
            return false;
        }

        $this->prods->delete($id);
        event(new ProdDeleted($id, $prodx->name, $prodx->des));
        return true;
    }

    public function deleteProds($root, array $args): bool
    {
        $ids = $args['ids'] ?? [];

        if (empty($ids)) {
            return false;
        }

        foreach ($ids as $id) {
            $prodx = $this->prods->findById($id);
            if ($prodx) {
                $this->prods->delete($id);
                event(new ProdDeleted($id, $prodx->name, $prodx->des));
            }
        }

        return true;
    }
}
