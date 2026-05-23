<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;
use JetBrains\PhpStorm\Pure;

enum FileTypes: string
{
    // GENERAL TYPES
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';
    case APPLICATION = 'application';
    case ARCHIVE = 'archive';
    case TEXT = 'text';
    case SCRIPT = 'script';
    case EXECUTABLE = 'executable';
    case EBOOK = 'ebook';
    case FONT = 'font';

    // IMAGE TYPES
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_PNG = 'image/png';
    case IMAGE_GIF = 'image/gif';
    case IMAGE_WEBP = 'image/webp';
    case IMAGE_BMP = 'image/bmp';
    case IMAGE_TIFF = 'image/tiff';
    case IMAGE_SVG = 'image/svg+xml';
    case IMAGE_PHOTOSHOP = 'image/vnd.adobe.photoshop';
    case IMAGE_ICON = 'image/x-icon';

    // VIDEO TYPES
    case VIDEO_MP4 = 'video/mp4';
    case VIDEO_AVI = 'video/x-msvideo';
    case VIDEO_MKV = 'video/x-matroska';
    case VIDEO_WEBM = 'video/webm';
    case VIDEO_MOV = 'video/quicktime';
    case VIDEO_MPEG = 'video/mpeg';
    case VIDEO_OGG = 'video/ogg';
    case VIDEO_3GPP = 'video/3gpp';
    case VIDEO_3GPP2 = 'video/3gpp2';

    // AUDIO TYPES
    case AUDIO_MP3 = 'audio/mpeg';
    case AUDIO_WAV = 'audio/wav';
    case AUDIO_OGG = 'audio/ogg';
    case AUDIO_FLAC = 'audio/flac';
    case AUDIO_M4A = 'audio/mp4';
    case AUDIO_AAC = 'audio/aac';
    case AUDIO_WMA = 'audio/x-ms-wma';

    // DOCUMENT TYPES
    case DOCUMENT_PDF = 'application/pdf';
    case DOCUMENT_WORD = 'application/msword';
    case DOCUMENT_EXCEL = 'application/vnd.ms-excel';
    case DOCUMENT_POWERPOINT = 'application/vnd.ms-powerpoint';

    // ARCHIVE TYPES
    case ARCHIVE_ZIP = 'application/zip';
    case ARCHIVE_RAR = 'application/x-rar-compressed';
    case ARCHIVE_7Z = 'application/x-7z-compressed';
    case ARCHIVE_GZIP = 'application/gzip';
    case ARCHIVE_TAR = 'application/x-tar';
    case ARCHIVE_BZIP = 'application/x-bzip';
    case ARCHIVE_BZIP2 = 'application/x-bzip2';
    case ARCHIVE_XZ = 'application/x-xz';

    //*--------------------------------------------*//
    // TEXT TYPES
    // SCRIPT TYPES
    // EXECUTABLE TYPES
    // EBOOK TYPES
    //*--------------------------------------------*//
    public static function imageMimeTypesLabels(): array
    {
        return [
            self::IMAGE_JPEG->value,
            self::IMAGE_PNG->value,
            self::IMAGE_GIF->value,
            self::IMAGE_WEBP->value,
            self::IMAGE_SVG->value,
            self::IMAGE_ICON->value,
        ];
    }
    public static function videoMimeTypesLabels(): array
    {
        return [
            self::VIDEO_MP4->value,
            self::VIDEO_AVI->value,
            self::VIDEO_MKV->value,
        ];
    }
    public static function audioMimeTypesLabels(): array
    {
        return [
            self::AUDIO_MP3->value,
            self::AUDIO_WAV->value,
            self::AUDIO_OGG->value,
            self::AUDIO_WMA->value,
        ];
    }

    public static function getFileType($mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return self::IMAGE->value;
        }
        else if (str_starts_with($mimeType, 'video/')) {
            return self::VIDEO->value;
        }
        else if (str_starts_with($mimeType, 'audio/')) {
            return self::AUDIO->value;
        }
        else if (str_starts_with($mimeType, 'application/')) {
            return self::APPLICATION->value;
        }
        else{
            return self::DOCUMENT->value;
        }
    }

}
