<?php

declare(strict_types=1);

namespace Symplify\DefaultAutowire\Tests\DependencyInjection\Definition\DefinitionAnalyzerSource;

use Symplify\DefaultAutowire\Tests\Source\SomeService;

final class NotMissingArgumentsTypehints
{
    public function __construct(SomeService $someService, SomeService $anotherService)
    {
    }
}