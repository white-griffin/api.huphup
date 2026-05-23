<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum FileAddresses: string
{
    /*** { Images Section } ***/
    case CATEGORY_IMAGE_PATH = 'uploads/categories/image/';
    case EXAM_IMAGE_PATH = 'uploads/exams/image/';
    case QUESTION_IMAGE_PATH = 'uploads/questions/image/';
    case OPTION_IMAGE_PATH = 'uploads/options/image/';


    /*** { Video Section } ***/
    case EXAM_VIDEO_PATH = 'uploads/exams/video/';
    case QUESTION_VIDEO_PATH = 'uploads/questions/video/';
    case OPTION_VIDEO_PATH = 'uploads/options/video/';

    /*** { Audio Section } ***/
    case EXAM_AUDIO_PATH = 'uploads/exams/audio/';
    case QUESTION_AUDIO_PATH = 'uploads/questions/audio/';
    case OPTION_AUDIO_PATH = 'uploads/options/audio/';


    public static function getImageAddress(string $className): ?string
    {
        $className = strtoupper($className);
        return constant("self::$className".'_IMAGE_PATH')->value ?? null;
    }



    public static function getVideoAddress(string $className): ?string
    {
        $className = strtoupper($className);
        return constant("self::$className".'_VIDEO_PATH')->value ?? null;
    }


    public static function getAudioAddress(string $className): ?string
    {
        $className = strtoupper($className);
        return constant("self::$className".'_AUDIO_PATH')->value ?? null;
    }
}
