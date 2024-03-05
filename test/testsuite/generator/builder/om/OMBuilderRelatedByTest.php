<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once __DIR__ . '/../../../../tools/helpers/bookstore/BookstoreTestBase.php';
require_once __DIR__ . '/../../../../../generator/lib/builder/om/OMBuilder.php';
require_once __DIR__ . '/../../../../../generator/lib/builder/util/XmlToAppData.php';
require_once __DIR__ . '/../../../../../generator/lib/platform/DefaultPlatform.php';

/**
 * Test class for OMBuilder.
 *
 * @author     François Zaninotto
 * @package    generator.builder.om
 */
class OMBuilderRelatedByTest extends \PHPUnit\Framework\TestCase
{
    public static $database;

    public function setUp(): void
    {
        // run only once to save execution time
        if (null == self::$database) {
            $xmlToAppData = new XmlToAppData(new DefaultPlatform());
            $appData = $xmlToAppData->parseFile(realpath(__DIR__ . '/../../../../fixtures/bookstore/schema.xml'));
            self::$database = $appData->getDatabase("bookstore");
        }
    }

    protected function getForeignKey($tableName, $index)
    {
        $fks = self::$database->getTable($tableName)->getForeignKeys();

        return $fks[$index];
    }

    public static function getRelatedBySuffixDataProvider()
    {
        return array(
            array('book', 0, '', ''),
            array('essay', 0, 'RelatedByFirstAuthor', 'RelatedByFirstAuthor'),
            array('essay', 1, 'RelatedBySecondAuthor', 'RelatedBySecondAuthor'),
            array('essay', 2, 'RelatedById', 'RelatedByNextEssayId'),
            array('bookstore_employee', 0, 'RelatedById', 'RelatedBySupervisorId'),
            array('composite_essay', 0, 'RelatedById0', 'RelatedByFirstEssayId'),
            array('composite_essay', 1, 'RelatedById1', 'RelatedBySecondEssayId'),
            array('man', 0, 'RelatedByWifeId', 'RelatedByWifeId'),
            array('woman', 0, 'RelatedByHusbandId', 'RelatedByHusbandId'),
        );
    }

    /**
     * @dataProvider getRelatedBySuffixDataProvider
     */
    public function testGetRelatedBySuffix($table, $index, $expectedSuffix, $expectedReverseSuffix)
    {
        $fk = $this->getForeignKey($table, $index);
        $this->assertEquals($expectedSuffix, TestableOMBuilder::getRefRelatedBySuffix($fk));
        $this->assertEquals($expectedReverseSuffix, TestableOMBuilder::getRelatedBySuffix($fk));
    }
}

class TestableOMBuilder extends OMBuilder
{
    public static function getRelatedBySuffix(ForeignKey $fk)
    {
        return parent::getRelatedBySuffix($fk);
    }

    public static function getRefRelatedBySuffix(ForeignKey $fk)
    {
        return parent::getRefRelatedBySuffix($fk);
    }

    public function getUnprefixedClassname() {}
}
