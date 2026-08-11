<?php

declare(strict_types=1);

namespace E107\Rector\DowngradePhp72\PhpDoc;

use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;

final readonly class NativeParamToPhpDocDecorator
{
    public function __construct(
        private PhpDocInfoFactory $phpDocInfoFactory,
        private NodeNameResolver $nodeNameResolver,
        private StaticTypeMapper $staticTypeMapper,
        private PhpDocTypeChanger $phpDocTypeChanger,
        private ValueResolver $valueResolver
    ) {
    }

    public function decorate(ClassMethod $classMethod, Param $param): void
    {
        if ($param->type === null) {
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        $paramName = $this->nodeNameResolver->getName($param);

        $mappedCurrentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $correctedNullableParamType = $this->correctNullableType($param, $mappedCurrentParamType);

        $this->phpDocTypeChanger->changeParamType(
            $classMethod,
            $phpDocInfo,
            $correctedNullableParamType,
            $param,
            $paramName
        );
    }

    private function isParamNullable(Param $param): bool
    {
        if (! $param->default instanceof Expr) {
            return false;
        }

        return $this->valueResolver->isNull($param->default);
    }

    private function correctNullableType(Param $param, Type $paramType): UnionType|Type
    {
        if (! $this->isParamNullable($param)) {
            return $paramType;
        }

        if (TypeCombinator::containsNull($paramType)) {
            return $paramType;
        }

        // add default null type
        return TypeCombinator::addNull($paramType);
    }
}
