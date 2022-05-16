<?php

declare(strict_types=1);

namespace App\Exception;

use App\Controller\IndexController;
use JetBrains\PhpStorm\Pure;

class ContentTypeNotFoundException extends \RuntimeException
{
    #[Pure]
    public function __construct(int $code = 0, \Throwable $previous = null)
    {
        $message = sprintf(
            "Aucune ID associé au content-type portant le nom '%s' a été trouvé.",
            IndexController::SEARCHED_CONTENT_TYPE
        );

        parent::__construct($message, $code, $previous);
    }
}
