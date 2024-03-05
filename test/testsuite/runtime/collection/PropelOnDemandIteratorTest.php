<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once __DIR__ . '/../../../tools/helpers/bookstore/BookstoreEmptyTestBase.php';

/**
 * Test class for PropelOnDemandIterator.
 *
 * @author     Francois Zaninotto
 * @package    runtime.collection
 */
class PropelOnDemandIteratorTest extends BookstoreEmptyTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        BookstoreDataPopulator::populate($this->con);
    }

    public function testInstancePoolingDisabled()
    {
        Propel::enableInstancePooling();
        $books = PropelQuery::from('Book')
            ->setFormatter(ModelCriteria::FORMAT_ON_DEMAND)
            ->find($this->con);
        foreach ($books as $book) {
            $this->assertFalse(Propel::isInstancePoolingEnabled());
        }
    }

    public function testInstancePoolingReenabled()
    {
        Propel::enableInstancePooling();
        $books = PropelQuery::from('Book')
            ->setFormatter(ModelCriteria::FORMAT_ON_DEMAND)
            ->find($this->con);
        foreach ($books as $book) {
        }
        $this->assertTrue(Propel::isInstancePoolingEnabled());

        Propel::disableInstancePooling();
        $books = PropelQuery::from('Book')
            ->setFormatter(ModelCriteria::FORMAT_ON_DEMAND)
            ->find($this->con);
        foreach ($books as $book) {
        }
        $this->assertFalse(Propel::isInstancePoolingEnabled());
        Propel::enableInstancePooling();
    }

}
