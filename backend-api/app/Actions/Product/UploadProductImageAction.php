<?php

namespace App\Actions\Product;

use App\DTOs\Product\UploadProductImageDTO;
use App\Models\ProductImage;
use App\Services\Product\ProductImageService;

class UploadProductImageAction
{
    public function __construct(
        protected ProductImageService $service
    ) {}

    public function execute(UploadProductImageDTO $dto): ProductImage
    {
        return $this->service->upload($dto);
    }
}
