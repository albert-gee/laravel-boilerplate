<?php
declare(strict_types=1);
namespace App\Http\Models;

/**
 * Class ErrorMessage
 * This class is used in order to generate error objects inside "errors" array in exception handler
 * @see Handler::render()
 * @package App\Exceptions
 */
class JsonError
{

    public $title;
    public $detail;

    /**
     * ErrorMessage constructor.
     * @param string $title
     * @param string $detail
     */
    public function __construct(string $title, string $detail)
    {
        $this->title = $title ?? "";
        $this->detail = $detail ?? "";
    }
}
