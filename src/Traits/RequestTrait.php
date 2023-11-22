<?php

declare(strict_types=1);

namespace ProdumanApi\Traits;

trait RequestTrait
{
    public function getRequestContent(): array
    {
        $objectVars = get_object_vars($this);

        array_walk_recursive($objectVars, function (&$var) {
            if (is_object($var) && method_exists($var, 'getRequestContent')) {
                $var = $var->getRequestContent();
            }
        });

        return $objectVars;
    }

    public static function dateTimeFieldFormat(array &$request, string $field, string $dateFormat = 'Y-m-d H:i:s'): void
    {
        if (isset($request[$field]) && $request[$field] instanceof \DateTimeInterface) {
            $request[$field] = $request[$field]->format($dateFormat);
        }
    }
}
