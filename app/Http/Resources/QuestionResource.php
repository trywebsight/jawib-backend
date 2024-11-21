<?php

namespace App\Http\Resources;

use App\Enums\QuestionMediaTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'question'              => $this->question,
            'question_media_type'   => $this->question_media_type,
            'question_media_url'    => media_url($this->question_media_url),
            'answer'                => $this->answer,
            'answer_media_url'      => media_url($this->answer_media_url),
            'level'                 => $this->level,
            'diff'                  => $this->diff,
            'category_id'           => $this->category_id,
        ];
    }
}
