<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once __DIR__ . '/../../../tools/helpers/BaseTestCase.php';
require_once __DIR__ . '/../../../../runtime/lib/query/Criteria.php';

Propel::init(__DIR__ . '/../../../fixtures/bookstore/build/conf/bookstore-conf.php');

/**
 * Test class for Join.
 *
 * @author     François Zaninotto
 * @package    runtime.query
 */
class JoinTest extends BaseTestCase
{
  /**
   * DB adapter saved for later.
   *
   * @var        DBAdapter
   */
  private $savedAdapter;

  protected function setUp(): void
  {
    parent::setUp();
    $this->savedAdapter = Propel::getDB(null);
    Propel::setDB(null, new DBSQLite());
  }

  protected function tearDown(): void
  {
    Propel::setDB(null, $this->savedAdapter);
    parent::tearDown();
  }

  public function testEmptyConditions()
  {
    $j = new Join();
    $this->assertEquals(array(), $j->getConditions());
  }

  public function testAddCondition()
  {
    $j = new Join();
    $j->addCondition('foo', 'bar');
    $this->assertEquals('=', $j->getOperator());
    $this->assertEquals('foo', $j->getLeftColumn());
    $this->assertEquals('bar', $j->getRightColumn());
  }

  public function testGetConditions()
  {
    $j = new Join();
    $j->addCondition('foo', 'bar');
    $expect = array(array('left' => 'foo', 'operator' => '=', 'right' => 'bar'));
    $this->assertEquals($expect, $j->getConditions());
  }

  public function testAddConditionWithOperator()
  {
    $j = new Join();
    $j->addCondition('foo', 'bar', '>=');
    $expect = array(array('left' => 'foo', 'operator' => '>=', 'right' => 'bar'));
    $this->assertEquals($expect, $j->getConditions());
  }

  public function testAddConditions()
  {
    $j = new Join();
    $j->addCondition('foo', 'bar');
    $j->addCondition('baz', 'bal');
    $expect = array(
      array('left' => 'foo', 'operator' => '=', 'right' => 'bar'),
      array('left' => 'baz', 'operator' => '=', 'right' => 'bal')
    );
    $this->assertEquals(array('=', '='), $j->getOperators());
    $this->assertEquals(array('foo', 'baz'), $j->getLeftColumns());
    $this->assertEquals(array('bar', 'bal'), $j->getRightColumns());
    $this->assertEquals($expect, $j->getConditions());
  }

  public function testAddExplicitConditionWithoutAlias()
  {
    $j = new Join();
    $j->addExplicitCondition('a', 'foo', null, 'b', 'bar', null);
    $this->assertEquals('=', $j->getOperator());
    $this->assertEquals('a.foo', $j->getLeftColumn());
    $this->assertEquals('b.bar', $j->getRightColumn());
    $this->assertEquals('a', $j->getLeftTableName());
    $this->assertEquals('b', $j->getRightTableName());
    $this->assertNull($j->getLeftTableAlias());
    $this->assertNull($j->getRightTableAlias());
    $this->assertEquals(1, $j->countConditions());
  }

    public function testAddExplicitconditionWithOneAlias()
    {
        $j = new Join();
        $j->setJoinType(Criteria::LEFT_JOIN);
        $j->addExplicitCondition('book', 'AUTHOR_ID', null, 'author', 'ID', 'a', Join::EQUAL);
        $params = array();
        $this->assertEquals($j->getClause($params), 'LEFT JOIN author a ON (book.AUTHOR_ID=a.ID)');
  }

  public function testAddExplicitConditionWithAlias()
  {
    $j = new Join();
    $j->addExplicitCondition('a', 'foo', 'Alias', 'b', 'bar', 'Blias');
    $this->assertEquals('=', $j->getOperator());
    $this->assertEquals('Alias.foo', $j->getLeftColumn());
    $this->assertEquals('Blias.bar', $j->getRightColumn());
    $this->assertEquals('a', $j->getLeftTableName());
    $this->assertEquals('b', $j->getRightTableName());
    $this->assertEquals('Alias', $j->getLeftTableAlias());
    $this->assertEquals('Blias', $j->getRightTableAlias());
  }

  public function testAddExplicitConditionWithOperator()
  {
    $j = new Join();
    $j->addExplicitCondition('a', 'foo', null, 'b', 'bar', null, '>=');
    $this->assertEquals('>=', $j->getOperator());
    $this->assertEquals('a.foo', $j->getLeftColumn());
    $this->assertEquals('b.bar', $j->getRightColumn());
  }

  public function testEmptyJoinType()
  {
    $j = new Join();
    $this->assertEquals(Join::INNER_JOIN, $j->getJoinType());
  }

  public function testSetJoinType()
  {
    $j = new Join();
    $j->setJoinType('foo');
    $this->assertEquals('foo', $j->getJoinType());
  }

  public function testSimpleConstructor()
  {
    $j = new Join('foo', 'bar', 'LEFT JOIN');
    $expect = array(array('left' => 'foo', 'operator' => '=', 'right' => 'bar'));
    $this->assertEquals($expect, $j->getConditions());
    $this->assertEquals('LEFT JOIN', $j->getJoinType());
  }

  public function testCompositeeConstructor()
  {
    $j = new Join(array('foo1', 'foo2'), array('bar1', 'bar2'), 'LEFT JOIN');
    $expect = array(
      array('left' => 'foo1', 'operator' => '=', 'right' => 'bar1'),
      array('left' => 'foo2', 'operator' => '=', 'right' => 'bar2')
    );
    $this->assertEquals($expect, $j->getConditions());
    $this->assertEquals('LEFT JOIN', $j->getJoinType());
  }

    public function testEquality()
    {
        $j1 = new Join('foo', 'bar', 'INNER JOIN');

        $j2 = new Join('foo', 'bar', 'INNER JOIN');
        $this->assertTrue($j1->equals($j2));

        $j3 = new Join('foo', 'bar', 'LEFT JOIN');
        $this->assertFalse($j1->equals($j3), 'INNER JOIN is not equal to LEFT JOIN');

        $j4 = new Join('foo', 'bar', 'RIGHT JOIN');
        $this->assertFalse($j1->equals($j4), 'INNER JOIN is not equal to RIGHT JOIN');

        $j5 = new Join('foo', 'bar');
        $j6 = new Join('foo', 'bar');
        $this->assertTrue($j5->equals($j6), 'Joins without specified join type should be equal,
                                                as they fallback to default join type');

        $j7 = new Join('foo', 'bar', 'INNER JOIN');
        $this->assertTrue($j5->equals($j7), 'Join without specified join type should be equal
                                                to INNER JOIN, as it fallback to default join type');

        $j8 = new Join('foo', 'bar', 'INNER JOIN');
        $j8->addCondition('baz.foo', 'baz.bar');
        $this->assertFalse($j5->equals($j8));
    }

  public function testCountConditions()
  {
    $j = new Join();
    $this->assertEquals(0, $j->countConditions());
    $j->addCondition('foo', 'bar');
    $this->assertEquals(1, $j->countConditions());
    $j->addCondition('foo1', 'bar1');
    $this->assertEquals(2, $j->countConditions());
  }

}
