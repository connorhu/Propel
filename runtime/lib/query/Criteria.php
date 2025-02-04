<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * This is a utility class for holding criteria information for a query.
 *
 * BasePeer constructs SQL statements based on the values in this class.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Kaspars Jaudzems <kaspars.jaudzems@inbox.lv> (Propel)
 * @author     Frank Y. Kim <frank.kim@clearink.com> (Torque)
 * @author     John D. McNally <jmcnally@collab.net> (Torque)
 * @author     Brett McLaughlin <bmclaugh@algx.net> (Torque)
 * @author     Eric Dobbs <eric@dobbse.net> (Torque)
 * @author     Henning P. Schmiedehausen <hps@intermeta.de> (Torque)
 * @author     Sam Joseph <sam@neurogrid.com> (Torque)
 * @package    propel.runtime.query
 */
class Criteria implements IteratorAggregate
{

    /** Comparison type. */
    public const EQUAL = "=";

    /** Comparison type. */
    public const NOT_EQUAL = "<>";

    /** Comparison type. */
    public const ALT_NOT_EQUAL = "!=";

    /** Comparison type. */
    public const GREATER_THAN = ">";

    /** Comparison type. */
    public const LESS_THAN = "<";

    /** Comparison type. */
    public const GREATER_EQUAL = ">=";

    /** Comparison type. */
    public const LESS_EQUAL = "<=";

    /** Comparison type. */
    public const LIKE = " LIKE ";

    /** Comparison type. */
    public const NOT_LIKE = " NOT LIKE ";

    /** Comparison for array column types */
    public const CONTAINS_ALL = "CONTAINS_ALL";

    /** Comparison for array column types */
    public const CONTAINS_SOME = "CONTAINS_SOME";

    /** Comparison for array column types */
    public const CONTAINS_NONE = "CONTAINS_NONE";

    /** PostgreSQL comparison type */
    public const ILIKE = " ILIKE ";

    /** PostgreSQL comparison type */
    public const NOT_ILIKE = " NOT ILIKE ";

    /** Comparison type. */
    public const CUSTOM = "CUSTOM";

    /** Comparison type */
    public const RAW = "RAW";

    /** Comparison type for update */
    public const CUSTOM_EQUAL = "CUSTOM_EQUAL";

    /** Comparison type. */
    public const DISTINCT = "DISTINCT";

    /** Comparison type. */
    public const IN = " IN ";

    /** Comparison type. */
    public const NOT_IN = " NOT IN ";

    /** Comparison type. */
    public const ALL = "ALL";

    /** Comparison type. */
    public const JOIN = "JOIN";

    /** Binary math operator: AND */
    public const BINARY_AND = "&";

    /** Binary math operator: OR */
    public const BINARY_OR = "|";

    /** "Order by" qualifier - ascending */
    public const ASC = "ASC";

    /** "Order by" qualifier - descending */
    public const DESC = "DESC";

    /** "IS NULL" null comparison */
    public const ISNULL = " IS NULL ";

    /** "IS NOT NULL" null comparison */
    public const ISNOTNULL = " IS NOT NULL ";

    /** "CURRENT_DATE" ANSI SQL function */
    public const CURRENT_DATE = "CURRENT_DATE";

    /** "CURRENT_TIME" ANSI SQL function */
    public const CURRENT_TIME = "CURRENT_TIME";

    /** "CURRENT_TIMESTAMP" ANSI SQL function */
    public const CURRENT_TIMESTAMP = "CURRENT_TIMESTAMP";

    /** "LEFT JOIN" SQL statement */
    public const LEFT_JOIN = "LEFT JOIN";

    /** "RIGHT JOIN" SQL statement */
    public const RIGHT_JOIN = "RIGHT JOIN";

    /** "INNER JOIN" SQL statement */
    public const INNER_JOIN = "INNER JOIN";

    /** logical OR operator */
    public const LOGICAL_OR = "OR";

    /** logical AND operator */
    public const LOGICAL_AND = "AND";

    protected $ignoreCase = false;
    protected $singleRecord = false;

    /**
     * Storage of select data. Collection of column names.
     *
     * @var        array
     */
    protected $selectColumns = array();

    /**
     * Storage of aliased select data. Collection of column names.
     *
     * @var        array
     */
    protected $asColumns = array();

    /**
     * Storage of select modifiers data. Collection of modifier names.
     *
     * @var        array
     */
    protected $selectModifiers = array();

    /**
     * Storage of conditions data. Collection of Criterion objects.
     *
     * @var        Criterion[]
     */
    protected $map = array();

    /**
     * Storage of ordering data. Collection of column names.
     *
     * @var        array
     */
    protected $orderByColumns = array();

    /**
     * Storage of grouping data. Collection of column names.
     *
     * @var        array
     */
    protected $groupByColumns = array();

    /**
     * Storage of having data.
     *
     * @var        Criterion
     */
    protected $having = null;

    /**
     * Storage of join data. collection of Join objects.
     *
     * @var        array
     */
    protected $joins = array();

    /**
     * @var        Criteria[]
     */
    protected $selectQueries = array();

    /**
     * The name of the database.
     *
     * @var        string
     */
    protected $dbName;

    /**
     * The primary table for this Criteria.
     * Useful in cases where there are no select or where
     * columns.
     *
     * @var        string
     */
    protected $primaryTableName = null;

    /** The name of the database as given in the constructor. */
    protected $originalDbName;

    /**
     * To limit the number of rows to return.  <code>0</code> means return all
     * rows.
     */
    protected $limit = 0;

    /** To start the results at a row other than the first one. */
    protected $offset = 0;

    /**
     * Comment to add to the SQL query
     *
     * @var        string
     */
    protected $queryComment = null;

    // flag to note that the criteria involves a blob.
    protected $blobFlag = null;

    protected $aliases = array();

    protected $useTransaction = false;

    /**
     * Storage for Criterions expected to be combined
     *
     * @var        array
     */
    protected $namedCriterions = array();

    /**
     * Default operator for combination of criterions
     *
     * @see        addUsingOperator
     * @var        string Criteria::LOGICAL_AND or Criteria::LOGICAL_OR
     */
    protected $defaultCombineOperator = Criteria::LOGICAL_AND;

    /**
     * Flags for boolean functions
     *
     * @var PropelConditionalProxy
     */
    protected $conditionalProxy = null;

    /**
     * Creates a new instance with the default capacity which corresponds to
     * the specified database.
     *
     * @param string $dbName The database name.
     */
    public function __construct($dbName = null)
    {
        $this->setDbName($dbName);
        $this->originalDbName = $dbName;
    }

    /**
     * Implementing SPL IteratorAggregate interface.  This allows
     * you to foreach () over a Criteria object.
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new CriterionIterator($this);
    }

    /**
     * Get the criteria map, i.e. the array of Criterions
     *
     * @return Criterion[]
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Brings this criteria back to its initial state, so that it
     * can be reused as if it was new. Except if the criteria has grown in
     * capacity, it is left at the current capacity.
     *
     * @return void
     */
    public function clear()
    {
        $this->map = array();
        $this->namedCriterions = array();
        $this->ignoreCase = false;
        $this->singleRecord = false;
        $this->selectModifiers = array();
        $this->selectColumns = array();
        $this->orderByColumns = array();
        $this->groupByColumns = array();
        $this->having = null;
        $this->asColumns = array();
        $this->joins = array();
        $this->selectQueries = array();
        $this->dbName = $this->originalDbName;
        $this->offset = 0;
        $this->limit = 0;
        $this->blobFlag = null;
        $this->aliases = array();
        $this->useTransaction = false;
        $this->conditionalProxy = null;
        $this->defaultCombineOperator = Criteria::LOGICAL_AND;
        $this->primaryTableName = null;
        $this->queryComment = null;

    }

    /**
     * Add an AS clause to the select columns. Usage:
     *
     * <code>
     * Criteria myCrit = new Criteria();
     * myCrit->addAsColumn("alias", "ALIAS(".MyPeer::ID.")");
     * </code>
     *
     * @param string $name   Wanted Name of the column (alias).
     * @param string $clause SQL clause to select from the table
     *
     * If the name already exists, it is replaced by the new clause.
     *
     * @return Criteria A modified Criteria object.
     */
    public function addAsColumn($name, $clause)
    {
        $this->asColumns[$name] = $clause;

        return $this;
    }

    /**
     * Get the column aliases.
     *
     * @return array An assoc array which map the column alias names
     * to the alias clauses.
     */
    public function getAsColumns()
    {
        return $this->asColumns;
    }

    /**
     * Returns the column name associated with an alias (AS-column).
     *
     * @param string $as
     *
     * @return string|null $string The name if found, null otherwise.
     */
    public function getColumnForAs($as)
    {
        if (isset($this->asColumns[$as])) {
            return $this->asColumns[$as];
        }

        return null;
    }

    /**
     * Allows one to specify an alias for a table that can
     * be used in various parts of the SQL.
     *
     * @param string $alias
     * @param string $table
     *
     * @return Criteria A modified Criteria object.
     */
    public function addAlias($alias, $table)
    {
        $this->aliases[$alias] = $table;

        return $this;
    }

    /**
     * Remove an alias for a table (useful when merging Criterias).
     *
     * @param string $alias
     *
     * @return Criteria A modified Criteria object.
     */
    public function removeAlias($alias)
    {
        unset($this->aliases[$alias]);

        return $this;
    }

    /**
     * Returns the aliases for this Criteria
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Returns the table name associated with an alias.
     *
     * @param string $alias
     *
     * @return string|null $string The name if given, null otherwise.
     */
    public function getTableForAlias($alias)
    {
        if (isset($this->aliases[$alias])) {
            return $this->aliases[$alias];
        }

        return null;
    }

    /**
     * Returns the table name and alias based on a table alias or name.
     * Use this method to get the details of a table name that comes in a clause,
     * which can be either a table name or an alias name.
     *
     * @param string $tableAliasOrName
     *
     * @return   array($tableName, $tableAlias)
     */
    public function getTableNameAndAlias($tableAliasOrName)
    {
        if (isset($this->aliases[$tableAliasOrName])) {
            return array($this->aliases[$tableAliasOrName], $tableAliasOrName);
        } else {
            return array($tableAliasOrName, null);
        }
    }

    /**
     * Get the keys of the criteria map, i.e. the list of columns bearing a condition
     * <code>
     * print_r($c->keys());
     *  => array('book.price', 'book.title', 'author.first_name')
     * </code>
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->map);
    }

    /**
     * Does this Criteria object contain the specified key?
     *
     * @param string $column [table.]column
     *
     * @return boolean True if this Criteria object contain the specified key.
     */
    public function containsKey($column)
    {
        // must use array_key_exists() because the key could
        // exist but have a NULL value (that'd be valid).
        return array_key_exists($column, $this->map);
    }

    /**
     * Does this Criteria object contain the specified key and does it have a value set for the key
     *
     * @param string $column [table.]column
     *
     * @return boolean True if this Criteria object contain the specified key and a value for that key
     */
    public function keyContainsValue($column)
    {
        // must use array_key_exists() because the key could
        // exist but have a NULL value (that'd be valid).
        return (array_key_exists($column, $this->map) && ($this->map[$column]->getValue() !== null));
    }

    /**
     * Whether this Criteria has any where columns.
     *
     * This counts conditions added with the add() method.
     *
     * @return boolean
     * @see        add()
     */
    public function hasWhereClause()
    {
        return !empty($this->map);
    }

    /**
     * Will force the sql represented by this criteria to be executed within
     * a transaction.  This is here primarily to support the oid type in
     * postgresql.  Though it can be used to require any single sql statement
     * to use a transaction.
     *
     * @param bool $v
     *
     * @return void
     */
    public function setUseTransaction($v)
    {
        $this->useTransaction = (boolean) $v;
    }

    /**
     * Whether the sql command specified by this criteria must be wrapped
     * in a transaction.
     *
     * @return boolean
     */
    public function isUseTransaction()
    {
        return $this->useTransaction;
    }

    /**
     * Method to return criteria related to columns in a table.
     *
     * Make sure you call containsKey($column) prior to calling this method,
     * since no check on the existence of the $column is made in this method.
     *
     * @param string $column Column name.
     *
     * @return Criterion A Criterion object.
     */
    public function getCriterion($column)
    {
        return $this->map[$column];
    }

    /**
     * Method to return the latest Criterion in a table.
     *
     * @return Criterion A Criterion or null no Criterion is added.
     */
    public function getLastCriterion()
    {
        if ($cnt = count($this->map)) {
            $map = array_values($this->map);

            return $map[$cnt - 1];
        }

        return null;
    }

    /**
     * Method to return criterion that is not added automatically
     * to this Criteria.  This can be used to chain the
     * Criterions to form a more complex where clause.
     *
     * @param string $column     Full name of column (for example TABLE.COLUMN).
     * @param mixed  $value
     * @param string $comparison
     *
     * @return Criterion
     */
    public function getNewCriterion($column, $value = null, $comparison = self::EQUAL)
    {
        return new Criterion($this, $column, $value, $comparison);
    }

    /**
     * Method to return a String table name.
     *
     * @param string $name Name of the key.
     *
     * @return string The value of the object at key.
     */
    public function getColumnName($name)
    {
        if (isset($this->map[$name])) {
            return $this->map[$name]->getColumn();
        }

        return null;
    }

    /**
     * Shortcut method to get an array of columns indexed by table.
     * <code>
     * print_r($c->getTablesColumns());
     *  => array(
     *       'book'   => array('book.price', 'book.title'),
     *       'author' => array('author.first_name')
     *     )
     * </code>
     *
     * @return array array(table => array(table.column1, table.column2))
     */
    public function getTablesColumns()
    {
        $tables = array();
        foreach ($this->keys() as $key) {
            $tableName = substr($key, 0, strrpos($key, '.'));
            $tables[$tableName][] = $key;
        }

        return $tables;
    }

    /**
     * Method to return a comparison String.
     *
     * @param string $key String name of the key.
     *
     * @return string A String with the value of the object at key.
     */
    public function getComparison($key)
    {
        if (isset($this->map[$key])) {
            return $this->map[$key]->getComparison();
        }

        return null;
    }

    /**
     * Get the Database(Map) name.
     *
     * @return string A String with the Database(Map) name.
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * Set the DatabaseMap name.  If <code>null</code> is supplied, uses value
     * provided by <code>Propel::getDefaultDB()</code>.
     *
     * @param string $dbName The Database (Map) name.
     *
     * @return void
     */
    public function setDbName($dbName = null)
    {
        $this->dbName = ($dbName === null ? Propel::getDefaultDB() : $dbName);
    }

    /**
     * Get the primary table for this Criteria.
     *
     * This is useful for cases where a Criteria may not contain
     * any SELECT columns or WHERE columns.  This must be explicitly
     * set, of course, in order to be useful.
     *
     * @return string
     */
    public function getPrimaryTableName()
    {
        return $this->primaryTableName;
    }

    /**
     * Sets the primary table for this Criteria.
     *
     * This is useful for cases where a Criteria may not contain
     * any SELECT columns or WHERE columns.  This must be explicitly
     * set, of course, in order to be useful.
     *
     * @param string $tableName
     */
    public function setPrimaryTableName($tableName)
    {
        $this->primaryTableName = $tableName;
    }

    /**
     * Method to return a String table name.
     *
     * @param string $name The name of the key.
     *
     * @return string The value of table for criterion at key.
     */
    public function getTableName($name)
    {
        if (isset($this->map[$name])) {
            return $this->map[$name]->getTable();
        }

        return null;
    }

    /**
     * Method to return the value that was added to Criteria.
     *
     * @param string $name A String with the name of the key.
     *
     * @return mixed The value of object at key.
     */
    public function getValue($name)
    {
        if (isset($this->map[$name])) {
            return $this->map[$name]->getValue();
        }

        return null;
    }

    /**
     * An alias to getValue() -- exposing a Hashtable-like interface.
     *
     * @param string $key An Object.
     *
     * @return mixed The value within the Criterion (not the Criterion object).
     */
    public function get($key)
    {
        return $this->getValue($key);
    }

    /**
     * Overrides Hashtable put, so that this object is returned
     * instead of the value previously in the Criteria object.
     * The reason is so that it more closely matches the behavior
     * of the add() methods. If you want to get the previous value
     * then you should first Criteria.get() it yourself. Note, if
     * you attempt to pass in an Object that is not a String, it will
     * throw a NPE. The reason for this is that none of the add()
     * methods support adding anything other than a String as a key.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return Criteria A modified Criteria object.
     */
    public function put($key, $value)
    {
        return $this->add($key, $value);
    }

    /**
     * Copies all of the mappings from the specified Map to this Criteria
     * These mappings will replace any mappings that this Criteria had for any
     * of the keys currently in the specified Map.
     *
     * if the map was another Criteria, its attributes are copied to this
     * Criteria, overwriting previous settings.
     *
     * @param mixed $t Mappings to be stored in this map.
     */
    public function putAll($t)
    {
        if (is_array($t)) {
            foreach ($t as $key => $value) {
                if ($value instanceof Criterion) {
                    $this->map[$key] = $value;
                } else {
                    $this->put($key, $value);
                }
            }
        } elseif ($t instanceof Criteria) {
            $this->joins = $t->joins;
        }
    }

    /**
     * This method adds a new criterion to the list of criterias.
     * If a criterion for the requested column already exists, it is
     * replaced. If is used as follow:
     *
     * <code>
     * $crit = new Criteria();
     * $crit->add($column, $value, Criteria::GREATER_THAN);
     * </code>
     *
     * Any comparison can be used.
     *
     * The name of the table must be used implicitly in the column name,
     * so the Column name must be something like 'TABLE.id'.
     *
     * @param string $critOrColumn The column to run the comparison on, or a Criterion object.
     * @param mixed  $value
     * @param string $comparison   A String.
     *
     * @return Criteria A modified Criteria object.
     */
    public function add($critOrColumn, $value = null, $comparison = null)
    {
        $criterion = $this->getCriterionForCondition($critOrColumn, $value, $comparison);
        if ($critOrColumn instanceof Criterion) {
            $this->map[$critOrColumn->getTable() . '.' . $critOrColumn->getColumn()] = $criterion;
        } else {
            $this->map[$critOrColumn] = $criterion;
        }

        return $this;
    }

    /**
     * This method creates a new criterion but keeps it for later use with combine()
     * Until combine() is called, the condition is not added to the query
     *
     * <code>
     * $crit = new Criteria();
     * $crit->addCond('cond1', $column1, $value1, Criteria::GREATER_THAN);
     * $crit->addCond('cond2', $column2, $value2, Criteria::EQUAL);
     * $crit->combine(array('cond1', 'cond2'), Criteria::LOGICAL_OR);
     * </code>
     *
     * Any comparison can be used.
     *
     * The name of the table must be used implicitly in the column name,
     * so the Column name must be something like 'TABLE.id'.
     *
     * @param string $name       name to combine the criterion later
     * @param string $p1         The column to run the comparison on, or Criterion object.
     * @param mixed  $value
     * @param string $comparison A String.
     *
     * @return Criteria A modified Criteria object.
     */
    public function addCond($name, $p1, $value = null, $comparison = null)
    {
        $this->namedCriterions[$name] = $this->getCriterionForCondition($p1, $value, $comparison);

        return $this;
    }

    /**
     * Combine several named criterions with a logical operator
     *
     * @param array  $criterions array of the name of the criterions to combine
     * @param string $operator   logical operator, either Criteria::LOGICAL_AND, or Criteria::LOGICAL_OR
     * @param string $name       optional name to combine the criterion later
     *
     * @return Criteria
     *
     * @throws PropelException
     */
    public function combine($criterions = array(), $operator = self::LOGICAL_AND, $name = null)
    {
        $operatorMethod = (strtoupper($operator) == self::LOGICAL_AND) ? 'addAnd' : 'addOr';
        $namedCriterions = array();
        foreach ($criterions as $key) {
            if (array_key_exists($key, $this->namedCriterions)) {
                $namedCriterions[] = $this->namedCriterions[$key];
                unset($this->namedCriterions[$key]);
            } else {
                throw new PropelException('Cannot combine unknown condition ' . $key);
            }
        }
        $firstCriterion = array_shift($namedCriterions);
        foreach ($namedCriterions as $criterion) {
            $firstCriterion->$operatorMethod($criterion);
        }
        if ($name === null) {
            $this->addAnd($firstCriterion, null, null);
        } else {
            $this->addCond($name, $firstCriterion, null, null);
        }

        return $this;
    }

    /**
     * This is the way that you should add a join of two tables.
     * Example usage:
     * <code>
     * $c->addJoin(ProjectPeer::ID, FooPeer::PROJECT_ID, Criteria::LEFT_JOIN);
     * // LEFT JOIN FOO ON (PROJECT.ID = FOO.PROJECT_ID)
     * </code>
     *
     * @param mixed $left     A String with the left side of the join, or an array (@see addMultipleJoin).
     * @param mixed $right    A String with the right side of the join, or an array (@see addMultipleJoin).
     * @param mixed $joinType A String with the join operator
     *                             among Criteria::INNER_JOIN, Criteria::LEFT_JOIN,
     *                             and Criteria::RIGHT_JOIN
     *
     * @return Criteria A modified Criteria object.
     */
    public function addJoin($left, $right, $joinType = null)
    {
        if (is_array($left)) {
            $conditions = array();
            foreach ($left as $key => $value) {
                $condition = array($value, $right[$key]);
                $conditions[] = $condition;
            }

            return $this->addMultipleJoin($conditions, $joinType);
        }

        $join = new Join();

        // is the left table an alias ?
        $dotpos = strrpos($left, '.');
        $leftTableAlias = substr($left, 0, $dotpos);
        $leftColumnName = substr($left, $dotpos + 1);
        list($leftTableName, $leftTableAlias) = $this->getTableNameAndAlias($leftTableAlias);

        // is the right table an alias ?
        $dotpos = strrpos($right, '.');
        $rightTableAlias = substr($right, 0, $dotpos);
        $rightColumnName = substr($right, $dotpos + 1);
        list($rightTableName, $rightTableAlias) = $this->getTableNameAndAlias($rightTableAlias);

        $join->addExplicitCondition(
            $leftTableName, $leftColumnName, $leftTableAlias,
            $rightTableName, $rightColumnName, $rightTableAlias,
            Join::EQUAL);

        $join->setJoinType($joinType);

        return $this->addJoinObject($join);
    }

    /**
     * Add a join with multiple conditions
     *
     * @deprecated use Join::setJoinCondition($criterion) instead
     *
     * @see http://propel.phpdb.org/trac/ticket/167, http://propel.phpdb.org/trac/ticket/606
     *
     * Example usage:
     * $c->addMultipleJoin(array(
     *     array(LeftPeer::LEFT_COLUMN, RightPeer::RIGHT_COLUMN),  // if no third argument, defaults to Criteria::EQUAL
     *     array(FoldersPeer::alias( 'fo', FoldersPeer::LFT ), FoldersPeer::alias( 'parent', FoldersPeer::RGT ), Criteria::LESS_EQUAL )
     *   ),
     *   Criteria::LEFT_JOIN
     * );
     *
     * @see        addJoin()
     *
     * @param array  $conditions An array of conditions, each condition being an array (left, right, operator)
     * @param string $joinType   A String with the join operator. Defaults to an implicit join.
     *
     * @return Criteria A modified Criteria object.
     */
    public function addMultipleJoin($conditions, $joinType = null)
    {
        $join = new Join();
        $joinCondition = null;
        foreach ($conditions as $condition) {
            $left = $condition[0];
            $right = $condition[1];
            $operator = isset($condition[2]) ? $condition[2] : JOIN::EQUAL;
            if ($pos = strrpos($left, '.')) {
                $leftTableAlias = substr($left, 0, $pos);
                $leftColumnName = substr($left, $pos + 1);
                list($leftTableName, $leftTableAlias) = $this->getTableNameAndAlias($leftTableAlias);
            } else {
                list($leftTableName, $leftTableAlias) = array(null, null);
                $leftColumnName = $left;
            }
            if (is_string($right) && $pos = strrpos($right, '.')) {
                $rightTableAlias = substr($right, 0, $pos);
                $rightColumnName = substr($right, $pos + 1);
                list($rightTableName, $rightTableAlias) = $this->getTableNameAndAlias($rightTableAlias);
                $conditionClause = $leftTableAlias ? $leftTableAlias . '.' : ($leftTableName ? $leftTableName . '.' : '');
                $conditionClause .= $leftColumnName;
                $conditionClause .= $operator;
                $conditionClause .= $rightTableAlias ? $rightTableAlias . '.' : ($rightTableName ? $rightTableName . '.' : '');
                $conditionClause .= $rightColumnName;
                $comparison = Criteria::CUSTOM;
            } else {
                list($rightTableName, $rightTableAlias) = array(null, null);
                $conditionClause = $right;
                $comparison = $operator;
            }
            if (!$join->getRightTableName()) {
                $join->setRightTableName($rightTableName);
            }
            if (!$join->getRightTableAlias()) {
                $join->setRightTableAlias($rightTableAlias);
            }
            $criterion = $this->getNewCriterion($leftTableName . '.' . $leftColumnName, $conditionClause, $comparison);
            if (null === $joinCondition) {
                $joinCondition = $criterion;
            } else {
                /* @var $joinCondition Criterion */
                $joinCondition = $joinCondition->addAnd($criterion);
            }
        }
        $join->setJoinType($joinType);
        $join->setJoinCondition($joinCondition);

        return $this->addJoinObject($join);
    }

    /**
     * Add a join object to the Criteria
     *
     * @param Join $join A join object
     *
     * @return Criteria A modified Criteria object
     */
    public function addJoinObject(Join $join)
    {
        $isAlreadyAdded = false;
        foreach ($this->joins as $alreadyAddedJoin) {
            if ($join->equals($alreadyAddedJoin)) {
                $isAlreadyAdded = true;
                break;
            }
        }

        if (!$isAlreadyAdded) {
            $this->joins[] = $join;
        }

        return $this;
    }

    /**
     * Get the array of Joins.
     *
     * @return array Join[]
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * Adds a Criteria as subQuery in the From Clause.
     *
     * @param Criteria $subQueryCriteria Criteria to build the subquery from
     * @param string   $alias            alias for the subQuery
     *
     * @return Criteria this modified Criteria object (Fluid API)
     */
    public function addSelectQuery(Criteria $subQueryCriteria, $alias = null)
    {
        if (null === $alias) {
            $alias = 'alias_' . ($subQueryCriteria->forgeSelectQueryAlias() + count($this->selectQueries));
        }
        $this->selectQueries[$alias] = $subQueryCriteria;

        return $this;
    }

    /**
     * Checks whether this Criteria has a subquery.
     *
     * @return boolean
     */
    public function hasSelectQueries()
    {
        return (bool) $this->selectQueries;
    }

    /**
     * Get the associative array of Criteria for the subQueries per alias.
     *
     * @return Criteria[]
     */
    public function getSelectQueries()
    {
        return $this->selectQueries;
    }

    /**
     * Get the Criteria for a specific subQuery.
     *
     * @param string $alias alias for the subQuery
     *
     * @return Criteria
     */
    public function getSelectQuery($alias)
    {
        return $this->selectQueries[$alias];
    }

    /**
     * checks if the Criteria for a specific subQuery is set.
     *
     * @param string $alias alias for the subQuery
     *
     * @return boolean
     */
    public function hasSelectQuery($alias)
    {
        return isset($this->selectQueries[$alias]);
    }

    public function forgeSelectQueryAlias()
    {
        $aliasNumber = 0;
        foreach ($this->getSelectQueries() as $c1) {
            /* @var $c1 Criteria */
            $aliasNumber += $c1->forgeSelectQueryAlias();
        }

        return ++$aliasNumber;
    }

    /**
     * Adds "ALL" modifier to the SQL statement.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function setAll()
    {
        $this->removeSelectModifier(self::DISTINCT);
        $this->addSelectModifier(self::ALL);

        return $this;
    }

    /**
     * Adds "DISTINCT" modifier to the SQL statement.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function setDistinct()
    {
        $this->removeSelectModifier(self::ALL);
        $this->addSelectModifier(self::DISTINCT);

        return $this;
    }

    /**
     * Adds a modifier to the SQL statement.
     * e.g. self::ALL, self::DISTINCT, 'SQL_CALC_FOUND_ROWS', 'HIGH_PRIORITY', etc.
     *
     * @param string $modifier The modifier to add
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function addSelectModifier($modifier)
    {
        //only allow the keyword once
        if (!$this->hasSelectModifier($modifier)) {
            $this->selectModifiers[] = $modifier;
        }

        return $this;
    }

    /**
     * Removes a modifier to the SQL statement.
     * Checks for existence before removal
     *
     * @param string $modifier The modifier to add
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function removeSelectModifier($modifier)
    {
        $this->selectModifiers = array_values(array_diff($this->selectModifiers, array($modifier)));

        return $this;
    }

    /**
     * Checks the existence of a SQL select modifier
     *
     * @param string $modifier The modifier to add
     *
     * @return bool
     */
    public function hasSelectModifier($modifier)
    {
        return in_array($modifier, $this->selectModifiers);
    }

    /**
     * Sets ignore case.
     *
     * @param boolean $b True if case should be ignored.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function setIgnoreCase($b)
    {
        $this->ignoreCase = (boolean) $b;

        return $this;
    }

    /**
     * Is ignore case on or off?
     *
     * @return boolean True if case is ignored.
     */
    public function isIgnoreCase()
    {
        return $this->ignoreCase;
    }

    /**
     * Set single record?  Set this to <code>true</code> if you expect the query
     * to result in only a single result record (the default behaviour is to
     * throw a PropelException if multiple records are returned when the query
     * is executed).  This should be used in situations where returning multiple
     * rows would indicate an error of some sort.  If your query might return
     * multiple records but you are only interested in the first one then you
     * should be using setLimit(1).
     *
     * @param boolean $b Set to TRUE if you expect the query to select just one record.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function setSingleRecord($b)
    {
        $this->singleRecord = (boolean) $b;

        return $this;
    }

    /**
     * Is single record?
     *
     * @return boolean True if a single record is being returned.
     */
    public function isSingleRecord()
    {
        return $this->singleRecord;
    }

    /**
     * Set limit.
     *
     * @param int $limit An int with the value for limit.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * Get limit.
     *
     * @return int An int with the value for limit.
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set offset.
     *
     * @param int $offset An int with the value for offset.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;

        return $this;
    }

    /**
     * Get offset.
     *
     * @return int An int with the value for offset.
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Add select column.
     *
     * @param string $name Name of the select column.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function addSelectColumn($name)
    {
        $this->selectColumns[] = $name;

        return $this;
    }

    /**
     * Set the query comment, that appears after the first verb in the SQL query
     *
     * @param string $comment The comment to add to the query, without comment sign
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function setComment($comment = null)
    {
        $this->queryComment = $comment;

        return $this;
    }

    /**
     * Get the query comment, that appears after the first verb in the SQL query
     *
     * @return string The comment to add to the query, without comment sign
     */
    public function getComment()
    {
        return $this->queryComment;
    }

    /**
     * Whether this Criteria has any select columns.
     *
     * This will include columns added with addAsColumn() method.
     *
     * @return boolean
     * @see        addAsColumn()
     * @see        addSelectColumn()
     */
    public function hasSelectClause()
    {
        return (!empty($this->selectColumns) || !empty($this->asColumns));
    }

    /**
     * Get select columns.
     *
     * @return array An array with the name of the select columns.
     */
    public function getSelectColumns()
    {
        return $this->selectColumns;
    }

    /**
     * Clears current select columns.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function clearSelectColumns()
    {
        $this->selectColumns = $this->asColumns = array();

        return $this;
    }

    /**
     * Get select modifiers.
     *
     * @return array An array with the select modifiers.
     */
    public function getSelectModifiers()
    {
        return $this->selectModifiers;
    }

    /**
     * Add group by column name.
     *
     * @param string $groupBy The name of the column to group by.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function addGroupByColumn($groupBy)
    {
        $this->groupByColumns[] = $groupBy;

        return $this;
    }

    /**
     * Add order by column name, explicitly specifying ascending.
     *
     * @param string $name The name of the column to order by.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function addAscendingOrderByColumn($name)
    {
        $this->orderByColumns[] = $name . ' ' . self::ASC;

        return $this;
    }

    /**
     * Add order by column name, explicitly specifying descending.
     *
     * @param string $name The name of the column to order by.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function addDescendingOrderByColumn($name)
    {
        $this->orderByColumns[] = $name . ' ' . self::DESC;

        return $this;
    }

    /**
     * Get order by columns.
     *
     * @return array An array with the name of the order columns.
     */
    public function getOrderByColumns()
    {
        return $this->orderByColumns;
    }

    /**
     * Clear the order-by columns.
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function clearOrderByColumns()
    {
        $this->orderByColumns = array();

        return $this;
    }

    /**
     * Clear the group-by columns.
     *
     * @return Criteria
     */
    public function clearGroupByColumns()
    {
        $this->groupByColumns = array();

        return $this;
    }

    /**
     * Get group by columns.
     *
     * @return array
     */
    public function getGroupByColumns()
    {
        return $this->groupByColumns;
    }

    /**
     * Get Having Criterion.
     *
     * @return Criterion A Criterion object that is the having clause.
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * Remove an object from the criteria.
     *
     * @param string $key A string with the key to be removed.
     *
     * @return mixed|null The removed value, null if not set.
     */
    public function remove($key)
    {
        if (isset($this->map[$key])) {
            $removed = $this->map[$key];
            unset($this->map[$key]);
            if ($removed instanceof Criterion) {
                return $removed->getValue();
            }

            return $removed;
        }

        return null;
    }

    /**
     * Build a string representation of the Criteria.
     *
     * @return string A String with the representation of the Criteria.
     */
    public function toString()
    {

        $sb = "Criteria:";
        try {

            $params = array();
            $sb .= "\nSQL (may not be complete): "
              . BasePeer::createSelectSql($this, $params);

            $sb .= "\nParams: ";
            $paramstr = array();
            foreach ($params as $param) {
                $paramstr[] = $param['table'] . '.' . $param['column'] . ' => ' . var_export($param['value'], true);
            }
            $sb .= implode(", ", $paramstr);
        } catch (Exception $exc) {
            $sb .= "(Error: " . $exc->getMessage() . ")";
        }

        return $sb;
    }

    /**
     * Returns the size (count) of this criteria.
     *
     * @return int
     */
    public function size()
    {
        return count($this->map);
    }

    /**
     * This method checks another Criteria to see if they contain
     * the same attributes and hashtable entries.
     *
     * @param Criteria|null $crit
     *
     * @return boolean
     */
    public function equals($crit)
    {
        if ($crit === null || !($crit instanceof Criteria)) {
            return false;
        } elseif ($this === $crit) {
            return true;
        } elseif ($this->size() === $crit->size()) {

            // Important: nested criterion objects are checked

            $criteria = $crit; // alias
            if  ($this->offset          === $criteria->getOffset()
                && $this->limit           === $criteria->getLimit()
                && $this->ignoreCase      === $criteria->isIgnoreCase()
                && $this->singleRecord    === $criteria->isSingleRecord()
                && $this->dbName          === $criteria->getDbName()
                && $this->selectModifiers === $criteria->getSelectModifiers()
                && $this->selectColumns   === $criteria->getSelectColumns()
                && $this->asColumns       === $criteria->getAsColumns()
                && $this->orderByColumns  === $criteria->getOrderByColumns()
                && $this->groupByColumns  === $criteria->getGroupByColumns()
                && $this->aliases         === $criteria->getAliases()
               ) // what about having ??
            {
                foreach ($criteria->keys() as $key) {
                    if ($this->containsKey($key)) {
                        $a = $this->getCriterion($key);
                        $b = $criteria->getCriterion($key);
                        if (!$a->equals($b)) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
                $joins = $criteria->getJoins();
                if (count($joins) != count($this->joins)) {
                    return false;
                }
                foreach ($joins as $key => $join) {
                    /* @var $join Join */
                    if (!$join->equals($this->joins[$key])) {
                        return false;
                    }
                }

                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Add the content of a Criteria to the current Criteria
     * In case of conflict, the current Criteria keeps its properties
     *
     * @param Criteria $criteria The criteria to read properties from
     * @param string   $operator The logical operator used to combine conditions
     *            Defaults to Criteria::LOGICAL_AND, also accepts Criteria::LOGICAL_OR
     *            This parameter is deprecated, use _or() instead
     *
     * @return Criteria The current criteria object
     *
     * @throws PropelException
     */
    public function mergeWith(Criteria $criteria, $operator = null)
    {
        // merge limit
        $limit = $criteria->getLimit();
        if ($limit != 0 && $this->getLimit() == 0) {
            $this->limit = $limit;
        }

        // merge offset
        $offset = $criteria->getOffset();
        if ($offset != 0 && $this->getOffset() == 0) {
            $this->offset = $offset;
        }

        // merge select modifiers
        $selectModifiers = $criteria->getSelectModifiers();
        if ($selectModifiers && !$this->selectModifiers) {
            $this->selectModifiers = $selectModifiers;
        }

        // merge select columns
        $this->selectColumns = array_merge($this->getSelectColumns(), $criteria->getSelectColumns());

        // merge as columns
        $commonAsColumns = array_intersect_key($this->getAsColumns(), $criteria->getAsColumns());
        if (!empty($commonAsColumns)) {
            throw new PropelException('The given criteria contains an AsColumn with an alias already existing in the current object');
        }
        $this->asColumns = array_merge($this->getAsColumns(), $criteria->getAsColumns());

        // merge orderByColumns
        $orderByColumns = array_merge($this->getOrderByColumns(), $criteria->getOrderByColumns());
        $this->orderByColumns = array_unique($orderByColumns);

        // merge groupByColumns
        $groupByColumns = array_merge($this->getGroupByColumns(), $criteria->getGroupByColumns());
        $this->groupByColumns = array_unique($groupByColumns);

        // merge where conditions
        if ($operator == Criteria::LOGICAL_OR) {
            $this->_or();
        }
        $isFirstCondition = true;
        foreach ($criteria->getMap() as $key => $criterion) {
            if ($isFirstCondition && $this->defaultCombineOperator == Criteria::LOGICAL_OR) {
                $this->addOr($criterion, null, null, false);
                $this->defaultCombineOperator = Criteria::LOGICAL_AND;
            } elseif ($this->containsKey($key)) {
                $this->addAnd($criterion);
            } else {
                $this->add($criterion);
            }
            $isFirstCondition = false;
        }

        // merge having
        if ($having = $criteria->getHaving()) {
            if ($this->getHaving()) {
                $this->addHaving($this->getHaving()->addAnd($having));
            } else {
                $this->addHaving($having);
            }
        }

        // merge alias
        $commonAliases = array_intersect_key($this->getAliases(), $criteria->getAliases());
        if (!empty($commonAliases)) {
            throw new PropelException('The given criteria contains an alias already existing in the current object');
        }
        $this->aliases = array_merge($this->getAliases(), $criteria->getAliases());

        // merge join
        $this->joins = array_merge($this->getJoins(), $criteria->getJoins());

        return $this;
    }

    /**
     * This method adds a prepared Criterion object to the Criteria as a having clause.
     * You can get a new, empty Criterion object with the
     * getNewCriterion() method.
     *
     * <p>
     * <code>
     * $crit = new Criteria();
     * $c = $crit->getNewCriterion(BasePeer::ID, 5, Criteria::LESS_THAN);
     * $crit->addHaving($c);
     * </code>
     *
     * @param mixed $p1         A Criterion, or a SQL clause with a question mark placeholder, or a column name
     * @param mixed $value      The value to bind in the condition
     * @param mixed $comparison A Criteria class constant, or a PDO::PARAM_ class constant
     *
     * @return Criteria Modified Criteria object (for fluent API)
     */
    public function addHaving($p1, $value = null, $comparison = null)
    {
        $this->having = $this->getCriterionForCondition($p1, $value, $comparison);

        return $this;
    }

    /**
     * Build a Criterion.
     *
     * This method has multiple signatures, and behaves differently according to it:
     *
     *  - If the first argument is a Criterion, it just returns this Criterion.
     *    <code>$c->getCriterionForCondition($criterion); // returns $criterion</code>
     *
     *  - If the last argument is a PDO::PARAM_* constant value, create a Criterion
     *    using Criteria::RAW and $comparison as a type.
     *    <code>$c->getCriterionForCondition('foo like ?', '%bar%', PDO::PARAM_STR);</code>
     *
     *  - Otherwise, create a classic Criterion based on a column name and a comparison.
     *    <code>$c->getCriterionForCondition(BookPeer::TITLE, 'War%', Criteria::LIKE);</code>
     *
     * @param mixed $p1         A Criterion, or a SQL clause with a question mark placeholder, or a column name
     * @param mixed $value      The value to bind in the condition
     * @param mixed $comparison A Criteria class constant, or a PDO::PARAM_ class constant
     *
     * @return Criterion
     */
    protected function getCriterionForCondition($p1, $value = null, $comparison = null)
    {
        if ($p1 instanceof Criterion) {
            // it's already a Criterion, so ignore $value and $comparison
            return $p1;
        } elseif (is_int($comparison)) {
            // $comparison is a PDO::PARAM_* constant value
            // something like $c->add('foo like ?', '%bar%', PDO::PARAM_STR);
            return new Criterion($this, $p1, $value, Criteria::RAW, $comparison);
        }

        // $comparison is one of Criteria's constants
        // something like $c->add(BookPeer::TITLE, 'War%', Criteria::LIKE);
        return new Criterion($this, $p1, $value, $comparison);
    }

    /**
     * If a criterion for the requested column already exists, the condition is "AND"ed to the existing criterion (necessary for Propel 1.4 compatibility).
     * If no criterion for the requested column already exists, the condition is "AND"ed to the latest criterion.
     * If no criterion exist, the condition is added a new criterion
     *
     * Any comparison can be used.
     *
     * Supports a number of different signatures:
     *  - addAnd(column, value, comparison)
     *  - addAnd(column, value)
     *  - addAnd(Criterion)
     *
     * @param mixed $p1                    A Criterion, or a SQL clause with a question mark placeholder, or a column name
     * @param mixed $value                 The value to bind in the condition
     * @param mixed $comparison            A Criteria class constant, or a PDO::PARAM_ class constant
     * @param bool  $preferColumnCondition
     *
     * @return Criteria A modified Criteria object.
     */
    public function addAnd($p1, $value = null, $comparison = null, $preferColumnCondition = true)
    {
        $criterion = $this->getCriterionForCondition($p1, $value, $comparison);

        $key = $criterion->getTable() . '.' . $criterion->getColumn();
        if ($preferColumnCondition && $this->containsKey($key)) {
            // FIXME: addAnd() operates preferably on existing conditions on the same column
            // this may cause unexpected results, but it's there for BC with Propel 14
            $this->getCriterion($key)->addAnd($criterion);
        } else {
            // simply add the condition to the list - this is the expected behavior
            $this->add($criterion);
        }

        return $this;
    }

    /**
     * If a criterion for the requested column already exists, the condition is "OR"ed to the existing criterion (necessary for Propel 1.4 compatibility).
     * If no criterion for the requested column already exists, the condition is "OR"ed to the latest criterion.
     * If no criterion exist, the condition is added a new criterion
     *
     * Any comparison can be used.
     *
     * Supports a number of different signatures:
     *  - addOr(column, value, comparison)
     *  - addOr(column, value)
     *  - addOr(Criterion)
     *
     * @param mixed $p1                    A Criterion, or a SQL clause with a question mark placeholder, or a column name
     * @param mixed $value                 The value to bind in the condition
     * @param mixed $comparison            A Criteria class constant, or a PDO::PARAM_ class constant
     * @param bool  $preferColumnCondition
     *
     * @return Criteria A modified Criteria object.
     */
    public function addOr($p1, $value = null, $comparison = null, $preferColumnCondition = true)
    {
        $rightCriterion = $this->getCriterionForCondition($p1, $value, $comparison);

        $key = $rightCriterion->getTable() . '.' . $rightCriterion->getColumn();
        if ($preferColumnCondition && $this->containsKey($key)) {
            // FIXME: addOr() operates preferably on existing conditions on the same column
            // this may cause unexpected results, but it's there for BC with Propel 14
            $leftCriterion = $this->getCriterion($key);
        } else {
            // fallback to the latest condition - this is the expected behavior
            $leftCriterion = $this->getLastCriterion();
        }

        if ($leftCriterion !== null) {
            // combine the given criterion with the existing one with an 'OR'
            $leftCriterion->addOr($rightCriterion);
        } else {
            // nothing to do OR / AND with, so make it first condition
            $this->add($rightCriterion);
        }

        return $this;
    }

    /**
     * Overrides Criteria::add() to use the default combine operator
     *
     * @see        Criteria::add()
     *
     * @param string|Criterion $p1                    The column to run the comparison on (e.g. BookPeer::ID), or Criterion object
     * @param mixed            $value
     * @param string           $operator              A String, like Criteria::EQUAL.
     * @param boolean          $preferColumnCondition If true, the condition is combined with an existing condition on the same column
     *                                                (necessary for Propel 1.4 compatibility).
     *                                                If false, the condition is combined with the last existing condition.
     *
     * @return Criteria A modified Criteria object.
     */
    public function addUsingOperator($p1, $value = null, $operator = null, $preferColumnCondition = true)
    {
        if ($this->defaultCombineOperator == Criteria::LOGICAL_OR) {
            $this->defaultCombineOperator = Criteria::LOGICAL_AND;

            return $this->addOr($p1, $value, $operator, $preferColumnCondition);
        } else {
            return $this->addAnd($p1, $value, $operator, $preferColumnCondition);
        }
    }

    // Fluid operators

    /**
     * @return Criteria
     */
    public function _or()
    {
        $this->defaultCombineOperator = Criteria::LOGICAL_OR;

        return $this;
    }

    /**
     * @return Criteria
     */
    public function _and()
    {
        $this->defaultCombineOperator = Criteria::LOGICAL_AND;

        return $this;
    }

    // Fluid Conditions

    /**
     * Returns the current object if the condition is true,
     * or a PropelConditionalProxy instance otherwise.
     * Allows for conditional statements in a fluid interface.
     *
     * @param bool $cond
     *
     * @return PropelConditionalProxy|Criteria
     */
    public function _if($cond)
    {
        $this->conditionalProxy = new PropelConditionalProxy($this, $cond, $this->conditionalProxy);

        return $this->conditionalProxy->getCriteriaOrProxy();
    }

    /**
     * Returns a PropelConditionalProxy instance.
     * Allows for conditional statements in a fluid interface.
     *
     * @param bool $cond ignored
     *
     * @return PropelConditionalProxy|Criteria
     *
     * @throws PropelException
     */
    public function _elseif($cond)
    {
        if (!$this->conditionalProxy) {
            throw new PropelException('_elseif() must be called after _if()');
        }

        return $this->conditionalProxy->_elseif($cond);
    }

    /**
     * Returns a PropelConditionalProxy instance.
     * Allows for conditional statements in a fluid interface.
     *
     * @return PropelConditionalProxy|Criteria
     *
     * @throws PropelException
     */
    public function _else()
    {
        if (!$this->conditionalProxy) {
            throw new PropelException('_else() must be called after _if()');
        }

        return $this->conditionalProxy->_else();
    }

    /**
     * Returns the current object
     * Allows for conditional statements in a fluid interface.
     *
     * @return Criteria
     *
     * @throws PropelException
     */
    public function _endif()
    {
        if (!$this->conditionalProxy) {
            throw new PropelException('_endif() must be called after _if()');
        }

        $this->conditionalProxy = $this->conditionalProxy->getParentProxy();

        if ($this->conditionalProxy) {
            return $this->conditionalProxy->getCriteriaOrProxy();
        }

        // reached last level
        return $this;
    }

    /**
     * Ensures deep cloning of attached objects
     */
    public function __clone()
    {
        foreach ($this->map as $key => $criterion) {
            $this->map[$key] = clone $criterion;
        }
        foreach ($this->joins as $key => $join) {
            $this->joins[$key] = clone $join;
        }
        if (null !== $this->having) {
            $this->having = clone $this->having;
        }
    }
}
