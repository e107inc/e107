<?php

declare (strict_types=1);
namespace E107\Rector\DowngradePhp81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\IntegerRangeType;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Vendored, const-free copy of
 * \Rector\DowngradePhp81\Rector\FuncCall\DowngradeHashAlgorithmXxHashRector.
 *
 * The upstream rule cannot be autoloaded on a host without the (removed) mhash
 * extension: its HASH_ALGORITHMS_TO_DOWNGRADE class constant dereferences the
 * \MHASH_XXH32 .. \MHASH_XXH128 constants at class-definition time, which fatals
 * with "Undefined constant MHASH_XXH32".
 *
 * This copy keeps the only portion that matters for source written by humans:
 * the string-literal algorithm-name path. Calls such as hash('xxh128', $data)
 * are downgraded by rewriting the algorithm argument to 'md5' (mirroring the
 * upstream REPLACEMENT_ALGORITHM). The algorithm set is expressed as plain
 * strings, so no MHASH_* constant is ever referenced.
 *
 * Dropped vs. upstream: the ConstFetch branch in getHashAlgorithm() and its
 * mapConstantToString()/constant() helper, which existed solely to translate an
 * \MHASH_XXH* constant token back to its string algorithm name. Source that
 * passes a literal MHASH_* constant to hash() would already fatal on PHP 5.6
 * (the constant does not exist there), so there is nothing useful to downgrade.
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeHashAlgorithmXxHash\DowngradeHashAlgorithmXxHashRectorTest
 */
final class DowngradeHashAlgorithmXxHashRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * Algorithms added in PHP 8.1 that do not exist on PHP 5.6.
     *
     * @var array<int, string>
     */
    private const HASH_ALGORITHMS_TO_DOWNGRADE = ['xxh32', 'xxh64', 'xxh3', 'xxh128'];
    /**
     * @var string
     */
    private const REPLACEMENT_ALGORITHM = 'md5';
    private int $argNamedKey;
    public function __construct(ArgsAnalyzer $argsAnalyzer, ValueResolver $valueResolver)
    {
        $this->argsAnalyzer = $argsAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade hash algorithm xxh32, xxh64, xxh3 or xxh128 by default to md5.', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return hash('xxh128', 'some-data-to-hash');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return hash('md5', 'some-data-to-hash');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?FuncCall
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if ($node->getAttribute(AttributeKey::PHP_VERSION_CONDITIONED)) {
            return null;
        }
        $this->argNamedKey = 0;
        $algorithm = $this->getHashAlgorithm($node->getArgs());
        if ($algorithm === null || !in_array($algorithm, self::HASH_ALGORITHMS_TO_DOWNGRADE, \true)) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[$this->argNamedKey])) {
            return null;
        }
        $arg = $args[$this->argNamedKey];
        $arg->value = new String_(self::REPLACEMENT_ALGORITHM);
        return $node;
    }
    private function shouldSkip(FuncCall $funcCall): bool
    {
        if ($funcCall->isFirstClassCallable()) {
            return \true;
        }
        if (!$this->isName($funcCall, 'hash')) {
            return \true;
        }
        $scope = ScopeFetcher::fetch($funcCall);
        $type = $scope->getPhpVersion()->getType();
        if (!$type instanceof IntegerRangeType) {
            return \false;
        }
        return $type->getMin() === 80100;
    }
    /**
     * @param Arg[] $args
     */
    private function getHashAlgorithm(array $args): ?string
    {
        if ($args === []) {
            return null;
        }
        $arg = null;
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            foreach ($args as $key => $arg) {
                if ((($nullsafeVariable1 = $arg->name) ? $nullsafeVariable1->name : null) !== 'algo') {
                    continue;
                }
                $this->argNamedKey = $key;
                break;
            }
        } else {
            $arg = $args[$this->argNamedKey] ?? null;
        }
        $algorithmNode = ($nullsafeVariable2 = $arg) ? $nullsafeVariable2->value : null;
        if ($algorithmNode instanceof String_) {
            return $this->valueResolver->getValue($algorithmNode);
        }
        return null;
    }
}
