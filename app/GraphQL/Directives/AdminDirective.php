<?php

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;

class AdminDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
directive @admin(
  read: Boolean = false
  write: Boolean = false
) on FIELD_DEFINITION
GRAPHQL;
    }

    public function handleField(FieldValue $fieldValue): void
    {
        $read  = (bool) $this->directiveArgValue('read');
        $write = (bool) $this->directiveArgValue('write');

        $fieldValue->wrapResolver(function (callable $resolver) use ($read, $write) {
            return function ($root, array $args, $context, $resolveInfo) use ($resolver, $read, $write) {
                $user = auth()->user();

                if (! $user) {
                    throw new AuthenticationException();
                }

                $level = (int) $user->is_admin;

                // Admin = 1 → everything
                if ($level === 1) {
                    return $resolver($root, $args, $context, $resolveInfo);
                }

                // Admin = 2 → READ only
                if ($level === 2 && $read && ! $write) {
                    return $resolver($root, $args, $context, $resolveInfo);
                }

                // Admin = 3 → both READ and WRITE 
                 
                if ($level === 3 && ($read || $write)) 
                { 
                    return $resolver($root, $args, $context, $resolveInfo); 
                }

                throw new AuthorizationException('Insufficient admin privileges');
            };
        });
    }
}
