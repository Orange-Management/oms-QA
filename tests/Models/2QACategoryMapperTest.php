<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\QA\tests\Models;

use Modules\QA\Models\NullQACategory;
use Modules\QA\Models\QACategory;
use Modules\QA\Models\QACategoryMapper;
use phpOMS\Utils\RnG\Text;

/**
 * @internal
 */
class QACategoryMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\QA\Models\QACategoryMapper
     * @group module
     */
    public function testCRUD() : void
    {
        $category = new QACategory();

        $category->setName('Test Category');

        $id = QACategoryMapper::create($category);
        self::assertGreaterThan(0, $category->getId());
        self::assertEquals($id, $category->getId());

        $categoryR = QACategoryMapper::get($category->getId());
        self::assertEquals($category->getName(), $categoryR->getName());
    }

    /**
     * @covers Modules\QA\Models\QACategoryMapper
     * @group module
     */
    public function testChildCRUD() : void
    {
        $category = new QACategory();

        $category->setName('Test Category2');
        $category->parent = new NullQACategory(1);

        $id = QACategoryMapper::create($category);
        self::assertGreaterThan(0, $category->getId());
        self::assertEquals($id, $category->getId());

        $categoryR = QACategoryMapper::get($category->getId());
        self::assertEquals($category->getName(), $categoryR->getName());
        self::assertEquals($category->parent->getId(), $categoryR->parent->getId());
    }

    /**
     * @covers Modules\QA\Models\QACategoryMapper
     * @group volume
     * @group module
     * @coversNothing
     */
    public function testVolume() : void
    {
        for ($i = 1; $i < 30; ++$i) {
            $text     = new Text();
            $category = new QACategory();

            $category->setName($text->generateText(\mt_rand(1, 3)));

            $id = QACategoryMapper::create($category);
        }
    }
}
