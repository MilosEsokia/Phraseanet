<?php

namespace Alchemy\Tests\Phrasea\Media\Type;

use Alchemy\Phrasea\Media\Type\Video;
use Alchemy\Phrasea\Media\Type\Type;

/**
 * @group functional
 * @group legacy
 */
class VideoTest extends \PhraseanetTestCase
{

    /**
     * @covers Alchemy\Phrasea\Media\Type\Video::getType
     */
    public function testGetType()
    {
        $object = new Video();
        $this->assertEquals(Type::TYPE_VIDEO, $object->getType());
    }
}
