<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Plain-language validation messages for an image upload field, quoting the
     * server's real size limit so owners know what to pick instead.
     *
     * @return array<string, string>
     */
    protected static function imageMessages(string $field): array
    {
        $limit = ['max' => round(upload_limit_kb() / 1024, 1)];

        return [
            "{$field}.uploaded" => __('app.image_upload_failed', $limit),
            "{$field}.file" => __('app.image_upload_failed', $limit),
            "{$field}.image" => __('app.image_wrong_type'),
            "{$field}.mimes" => __('app.image_wrong_type'),
            "{$field}.max" => __('app.image_too_large', $limit),
        ];
    }
}
