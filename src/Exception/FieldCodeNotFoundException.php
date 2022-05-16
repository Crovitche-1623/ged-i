<?php

declare(strict_types=1);

namespace App\Exception;

use App\Controller\IndexController;
use JetBrains\PhpStorm\Pure;

class FieldCodeNotFoundException extends \RuntimeException
{
    #[Pure]
    public function __construct(
        int $collaboratorFolderId,
        int $code = 0,
        \Throwable $previous = null
    )
    {
        $message = sprintf(
            "Aucun code de champ trouvé pour le Title '%s' dans la structure de données du type de contenu %s",
            IndexController::SEARCHED_FIELD,
            IndexController::SEARCHED_CONTENT_TYPE . ' (' . $collaboratorFolderId . ')'
        );

        parent::__construct($message, $code, $previous);
    }
}
