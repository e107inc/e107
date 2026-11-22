<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

class AnonymousWithArguments
{
    public function parse($matches, $result)
    {
        return new class ($matches, $result)
        {
            public $matches;

            public function __construct($matches, $result)
            {
                $this->matches = $matches;
            }
        };
    }
}
-----
<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

spl_autoload_register(function ($class) {
    if ($class === 'E107\Rector\Tests\Downgrade\Fixture\Anonymous__%x__0') {
        class Anonymous__%x__0
        {
            public $matches;
            public function __construct($matches, $result)
            {
                $this->matches = $matches;
            }
        }
    }
});
class AnonymousWithArguments
{
    public function parse($matches, $result)
    {
        return new Anonymous__%x__0($matches, $result);
    }
}
