<?php

namespace App\Models;

use App\Enums\QuestionMediaTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = ['question_media_type', 'answer_media_type'];
    protected $casts = [
        'options' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getOptionsAttribute($value)
    {
        $options = [
            'hide_media'        => false,
            'hide_media_after'  => null,
        ];
        if (is_null($value)) {
            return $options;
        }
        // Decode the JSON string into an array (assuming it's stored as a JSON string in the database)
        $decodedOptions = json_decode($value, true);

        // If decoding fails (i.e. value is not a valid JSON string), return the default options
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $options;
        }

        // Ensure 'hide_media' and 'hide_media_after' are set to non-null values
        $decodedOptions['hide_media'] = $decodedOptions['hide_media'] ?? false;
        $decodedOptions['hide_media_after'] = $decodedOptions['hide_media_after'] ?? null;
        // Return the final options array
        return $decodedOptions;
    }

    public function getQuestionMediaTypeAttribute()
    {
        return $this->checkMediaType($this->question_media_url);
    }
    public function getAnswerMediaTypeAttribute()
    {
        return $this->checkMediaType($this->answer_media_url);
    }

    private function checkMediaType($link)
    {
        // Extract the file extension from question_media_url
        $fileExtension = strtolower(pathinfo($link, PATHINFO_EXTENSION));
        return match (true) {
            in_array($fileExtension, QuestionMediaTypeEnum::IMAGE->getExtensions()) => QuestionMediaTypeEnum::IMAGE->value,
            in_array($fileExtension, QuestionMediaTypeEnum::VIDEO->getExtensions()) => QuestionMediaTypeEnum::VIDEO->value,
            in_array($fileExtension, QuestionMediaTypeEnum::AUDIO->getExtensions()) => QuestionMediaTypeEnum::AUDIO->value,
            default => QuestionMediaTypeEnum::TEXT->value, // Default to TEXT if no match
        };
    }

    // Local Scopes
    public function scopeSystem($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeUser($query)
    {
        return $query->whereNotNull('user_id');
    }
}
