<?php

namespace App\Contract;

use App\Dto\ImageDto;

interface ImageProviderInterface
{
    public function getImageById(string $id): ?ImageDto;

    /**
     * @return ImageDto[]
     */
    public function getImagesByQuery(string $query, ?string $lang): array;
}
