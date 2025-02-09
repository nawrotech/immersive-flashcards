<?php

namespace App\Contract;

use App\Dto\ImageDto;

interface ImageProviderInterface
{
    /**
     * @return ImageDto[]
     */
    public function getImagesByQuery(string $query, ?string $lang): array;
}
