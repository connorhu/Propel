<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

require_once __DIR__ . '/ObjectBuilder.php';

/**
 * Generates a PHP5 tree node Object class for user object model (OM) using Nested Set way.
 *
 * This class produces the base tree node object class (e.g. BaseMyTableNestedSet) which contains all
 * the custom-built accessor and setter methods.
 *
 * @author     Heltem <heltem@o2php.com>
 * @package    propel.generator.builder.om
 */
class PHP5NestedSetBuilder extends ObjectBuilder
{

    /**
     * Gets the package for the [base] object classes.
     *
     * @return string
     */
    public function getPackage()
    {
        return parent::getPackage() . ".om";
    }

    /**
     * Returns the name of the current class being built.
     *
     * @return string
     */
    public function getUnprefixedClassname()
    {
        return $this->getBuildProperty('basePrefix') . $this->getStubObjectBuilder()->getUnprefixedClassname() . 'NestedSet';
    }

    /**
     * Adds the include() statements for files that this class depends on or utilizes.
     *
     * @param string &$script The script will be modified in this method.
     */
    protected function addIncludes(&$script)
    {
        $script .= "
require '" . $this->getObjectBuilder()->getClassFilePath() . "';
";
    } // addIncludes()

    /**
     * Adds class phpdoc comment and opening of class.
     *
     * @param string &$script The script will be modified in this method.
     */
    protected function addClassOpen(&$script)
    {

        $table = $this->getTable();
        $tableName = $table->getName();
        $tableDesc = $table->getDescription();

        $script .= "
/**
 * Base class that represents a row from the '$tableName' table.
 *
 * $tableDesc
 *";
        if ($this->getBuildProperty('addTimeStamp')) {
            $now = strftime('%c');
            $script .= "
 * This class was autogenerated by Propel " . $this->getBuildProperty('version') . " on:
 *
 * $now
 *";
        }
        $script .= "
 * @deprecated  Since Propel 1.5. Use the nested_set behavior instead of the NestedSet treeMode
 * @package    propel.generator." . $this->getPackage() . "
 */
abstract class " . $this->getClassname() . " extends " . $this->getObjectBuilder()->getClassname() . " implements NodeObject {
";
    }

    /**
     * Specifies the methods that are added as part of the basic OM class.
     * This can be overridden by subclasses that wish to add more methods.
     *
     * @see        ObjectBuilder::addClassBody()
     */
    protected function addClassBody(&$script)
    {
        $table = $this->getTable();

        $this->addAttributes($script);

        $this->addGetIterator($script);

        $this->addSave($script);
        $this->addDelete($script);

        $this->addMakeRoot($script);

        $this->addGetLevel($script);
        $this->addGetPath($script);

        $this->addGetNumberOfChildren($script);
        $this->addGetNumberOfDescendants($script);

        $this->addGetChildren($script);
        $this->addGetDescendants($script);

        $this->addSetLevel($script);

        $this->addSetChildren($script);
        $this->addSetParentNode($script);
        $this->addSetPrevSibling($script);
        $this->addSetNextSibling($script);

        $this->addIsRoot($script);
        $this->addIsLeaf($script);
        $this->addIsEqualTo($script);

        $this->addHasParent($script);
        $this->addHasChildren($script);
        $this->addHasPrevSibling($script);
        $this->addHasNextSibling($script);

        $this->addRetrieveParent($script);
        $this->addRetrieveFirstChild($script);
        $this->addRetrieveLastChild($script);
        $this->addRetrievePrevSibling($script);
        $this->addRetrieveNextSibling($script);

        $this->addInsertAsFirstChildOf($script);
        $this->addInsertAsLastChildOf($script);

        $this->addInsertAsPrevSiblingOf($script);
        $this->addInsertAsNextSiblingOf($script);

        $this->addMoveToFirstChildOf($script);
        $this->addMoveToLastChildOf($script);

        $this->addMoveToPrevSiblingOf($script);
        $this->addMoveToNextSiblingOf($script);

        $this->addInsertAsParentOf($script);

        $this->addGetLeft($script);
        $this->addGetRight($script);
        $this->addGetScopeId($script);

        $this->addSetLeft($script);
        $this->addSetRight($script);
        $this->addSetScopeId($script);
    }

    /**
     * Closes class.
     *
     * @param string &$script The script will be modified in this method.
     */
    protected function addClassClose(&$script)
    {
        $script .= "
} // " . $this->getClassname() . "
";
    }

    /**
     * Adds class attributes.
     *
     * @param string &$script The script will be modified in this method.
     */
    protected function addAttributes(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $script .= "
    /**
     * Store level of node
     * @var        int
     */
    protected \$level = null;

    /**
     * Store if node has prev sibling
     * @var        bool
     */
    protected \$hasPrevSibling = null;

    /**
     * Store node if has prev sibling
     * @var        $objectClassName
     */
    protected \$prevSibling = null;

    /**
     * Store if node has next sibling
     * @var        bool
     */
    protected \$hasNextSibling = null;

    /**
     * Store node if has next sibling
     * @var        $objectClassName
     */
    protected \$nextSibling = null;

    /**
     * Store if node has parent node
     * @var        bool
     */
    protected \$hasParentNode = null;

    /**
     * The parent node for this node.
     * @var        $objectClassName
     */
    protected \$parentNode = null;

    /**
     * Store children of the node
     * @var        array
     */
    protected \$_children = null;
";
    }

    protected function addGetIterator(&$script)
    {
        $script .= "
    /**
     * Returns a pre-order iterator for this node and its children.
     *
     * @return NodeIterator
     */
    public function getIterator()
    {
        return new NestedSetRecursiveIterator(\$this);
    }
";
    }

    protected function addSave(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Saves modified object data to the datastore.
     * If object is saved without left/right values, set them as undefined (0)
     *
     * @param      PropelPDO Connection to use.
     * @return int The number of rows affected by this insert/update and any referring fk objects' save() operations.
     *                 May be unreliable with parent/children/brother changes
     * @throws PropelException
     */
    public function save(PropelPDO \$con = null)
    {
        \$left = \$this->getLeftValue();
        \$right = \$this->getRightValue();
        if (empty(\$left) || empty(\$right)) {
            \$root = $peerClassname::retrieveRoot(\$this->getScopeIdValue(), \$con);
            $peerClassname::insertAsLastChildOf(\$this, \$root, \$con);
        }

        return parent::save(\$con);
    }
";
    }

    protected function addDelete(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Removes this object and all descendants from datastore.
     *
     * @param      PropelPDO Connection to use.
     * @return void
     * @throws PropelException
     */
    public function delete(PropelPDO \$con = null)
    {
        // delete node first
        parent::delete(\$con);

        // delete descendants and then shift tree
        $peerClassname::deleteDescendants(\$this, \$con);
    }
";
    }

    protected function addMakeRoot(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Sets node properties to make it a root node.
     *
     * @return                 $objectClassName The current object (for fluent API support)
     * @throws PropelException
     */
    public function makeRoot()
    {
        $peerClassname::createRoot(\$this);

        return \$this;
    }
";
    }

    protected function addGetLevel(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Gets the level if set, otherwise calculates this and returns it
     *
     * @param      PropelPDO Connection to use.
     * @return int
     */
    public function getLevel(PropelPDO \$con = null)
    {
        if (null === \$this->level) {
            \$this->level = $peerClassname::getLevel(\$this, \$con);
        }

        return \$this->level;
    }
";
    }

    protected function addSetLevel(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $script .= "
    /**
     * Sets the level of the node in the tree
     *
     * @param      int \$v new value
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function setLevel(\$level)
    {
        \$this->level = \$level;

        return \$this;
    }
";
    }

    protected function addSetChildren(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $script .= "
    /**
     * Sets the children array of the node in the tree
     *
     * @param  array of $objectClassName \$children	array of Propel node object
     * @return          $objectClassName The current object (for fluent API support)
     */
    public function setChildren(array \$children)
    {
        \$this->_children = \$children;

        return \$this;
    }
";
    }

    protected function addSetParentNode(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Sets the parentNode of the node in the tree
     *
     * @param    $objectClassName \$parent Propel node object
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function setParentNode(NodeObject \$parent = null)
    {
        \$this->parentNode = (true === (\$this->hasParentNode = $peerClassname::isValid(\$parent))) ? \$parent : null;

        return \$this;
    }
";
    }

    protected function addSetPrevSibling(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Sets the previous sibling of the node in the tree
     *
     * @param    $objectClassName \$node Propel node object
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function setPrevSibling(NodeObject \$node = null)
    {
        \$this->prevSibling = \$node;
        \$this->hasPrevSibling = $peerClassname::isValid(\$node);

        return \$this;
    }
";
    }

    protected function addSetNextSibling(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Sets the next sibling of the node in the tree
     *
     * @param    $objectClassName \$node Propel node object
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function setNextSibling(NodeObject \$node = null)
    {
        \$this->nextSibling = \$node;
        \$this->hasNextSibling = $peerClassname::isValid(\$node);

        return \$this;
    }
";
    }

    protected function addGetPath(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Get the path to the node in the tree
     *
     * @param      PropelPDO Connection to use.
     * @return array
     */
    public function getPath(PropelPDO \$con = null)
    {
        return $peerClassname::getPath(\$this, \$con);
    }
";
    }

    protected function addGetNumberOfChildren(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Gets the number of children for the node (direct descendants)
     *
     * @param      PropelPDO Connection to use.
     * @return int
     */
    public function getNumberOfChildren(PropelPDO \$con = null)
    {
        return $peerClassname::getNumberOfChildren(\$this, \$con);
    }
";
    }

    protected function addGetNumberOfDescendants(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Gets the total number of descendants for the node
     *
     * @param      PropelPDO Connection to use.
     * @return int
     */
    public function getNumberOfDescendants(PropelPDO \$con = null)
    {
        return $peerClassname::getNumberOfDescendants(\$this, \$con);
    }
";
    }

    protected function addGetChildren(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Gets the children for the node
     *
     * @param      PropelPDO Connection to use.
     * @return array
     */
    public function getChildren(PropelPDO \$con = null)
    {
        \$this->getLevel();

        if (is_array(\$this->_children)) {
            return \$this->_children;
        }

        return $peerClassname::retrieveChildren(\$this, \$con);
    }
";
    }

    protected function addGetDescendants(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Gets the descendants for the node
     *
     * @param      PropelPDO Connection to use.
     * @return array
     */
    public function getDescendants(PropelPDO \$con = null)
    {
        \$this->getLevel();

        return $peerClassname::retrieveDescendants(\$this, \$con);
    }
";
    }

    protected function addIsRoot(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Returns true if node is the root node of the tree.
     *
     * @return bool
     */
    public function isRoot()
    {
        return $peerClassname::isRoot(\$this);
    }
";
    }

    protected function addIsLeaf(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Return true if the node is a leaf node
     *
     * @return bool
     */
    public function isLeaf()
    {
        return $peerClassname::isLeaf(\$this);
    }
";
    }

    protected function addIsEqualTo(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Tests if object is equal to \$node
     *
     * @param      object \$node		Propel object for node to compare to
     * @return bool
     */
    public function isEqualTo(NodeObject \$node)
    {
        return $peerClassname::isEqualTo(\$this, \$node);
    }
";
    }

    protected function addHasParent(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Tests if object has an ancestor
     *
     * @param      PropelPDO \$con Connection to use.
     * @return bool
     */
    public function hasParent(PropelPDO \$con = null)
    {
        if (null === \$this->hasParentNode) {
            $peerClassname::hasParent(\$this, \$con);
        }

        return \$this->hasParentNode;
    }
";
    }

    protected function addHasChildren(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Determines if the node has children / descendants
     *
     * @return bool
     */
    public function hasChildren()
    {
        return  $peerClassname::hasChildren(\$this);
    }
";
    }

    protected function addHasPrevSibling(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Determines if the node has previous sibling
     *
     * @param      PropelPDO \$con Connection to use.
     * @return bool
     */
    public function hasPrevSibling(PropelPDO \$con = null)
    {
        if (null === \$this->hasPrevSibling) {
            $peerClassname::hasPrevSibling(\$this, \$con);
        }

        return \$this->hasPrevSibling;
    }
";
    }

    protected function addHasNextSibling(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Determines if the node has next sibling
     *
     * @param      PropelPDO \$con Connection to use.
     * @return bool
     */
    public function hasNextSibling(PropelPDO \$con = null)
    {
        if (null === \$this->hasNextSibling) {
            $peerClassname::hasNextSibling(\$this, \$con);
        }

        return \$this->hasNextSibling;
    }
";
    }

    protected function addRetrieveParent(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Gets ancestor for the given node if it exists
     *
     * @param      PropelPDO \$con Connection to use.
     * @return mixed Propel object if exists else false
     */
    public function retrieveParent(PropelPDO \$con = null)
    {
        if (null === \$this->hasParentNode) {
            \$this->parentNode = $peerClassname::retrieveParent(\$this, \$con);
            \$this->hasParentNode = $peerClassname::isValid(\$this->parentNode);
        }

        return \$this->parentNode;
    }
";
    }

    protected function addRetrieveFirstChild(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Gets first child if it exists
     *
     * @param      PropelPDO \$con Connection to use.
     * @return mixed Propel object if exists else false
     */
    public function retrieveFirstChild(PropelPDO \$con = null)
    {
        if (\$this->hasChildren(\$con)) {
            if (is_array(\$this->_children)) {
                return \$this->_children[0];
            }

            return $peerClassname::retrieveFirstChild(\$this, \$con);
        }

        return false;
    }
";
    }

    protected function addRetrieveLastChild(&$script)
    {
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Gets last child if it exists
     *
     * @param      PropelPDO \$con Connection to use.
     * @return mixed Propel object if exists else false
     */
    public function retrieveLastChild(PropelPDO \$con = null)
    {
        if (\$this->hasChildren(\$con)) {
            if (is_array(\$this->_children)) {
                \$last = count(\$this->_children) - 1;

                return \$this->_children[\$last];
            }

            return $peerClassname::retrieveLastChild(\$this, \$con);
        }

        return false;
    }
";
    }

    protected function addRetrievePrevSibling(&$script)
    {
        $script .= "
    /**
     * Gets prev sibling for the given node if it exists
     *
     * @param      PropelPDO \$con Connection to use.
     * @return mixed Propel object if exists else false
     */
    public function retrievePrevSibling(PropelPDO \$con = null)
    {
        if (\$this->hasPrevSibling(\$con)) {
            return \$this->prevSibling;
        }

        return \$this->hasPrevSibling;
    }
";
    }

    protected function addRetrieveNextSibling(&$script)
    {
        $script .= "
    /**
     * Gets next sibling for the given node if it exists
     *
     * @param      PropelPDO \$con Connection to use.
     * @return mixed Propel object if exists else false
     */
    public function retrieveNextSibling(PropelPDO \$con = null)
    {
        if (\$this->hasNextSibling(\$con)) {
            return \$this->nextSibling;
        }

        return \$this->hasNextSibling;
    }
";
    }

    protected function addInsertAsFirstChildOf(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Inserts as first child of given destination node \$parent
     *
     * @param   $objectClassName \$parent	Propel object for destination node
     * @param      PropelPDO \$con Connection to use.
     * @return                 $objectClassName The current object (for fluent API support)
     * @throws PropelException - if this object already exists
     */
    public function insertAsFirstChildOf(NodeObject \$parent, PropelPDO \$con = null)
    {
        if (!\$this->isNew()) {
            throw new PropelException(\"$objectClassName must be new.\");
        }
        $peerClassname::insertAsFirstChildOf(\$this, \$parent, \$con);

        return \$this;
    }
";
    }

    protected function addInsertAsLastChildOf(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Inserts as last child of given destination node \$parent
     *
     * @param   $objectClassName \$parent	Propel object for destination node
     * @param      PropelPDO \$con Connection to use.
     * @return                 $objectClassName The current object (for fluent API support)
     * @throws PropelException - if this object already exists
     */
    public function insertAsLastChildOf(NodeObject \$parent, PropelPDO \$con = null)
    {
        if (!\$this->isNew()) {
            throw new PropelException(\"$objectClassName must be new.\");
        }
        $peerClassname::insertAsLastChildOf(\$this, \$parent, \$con);

        return \$this;
    }
";
    }

    protected function addInsertAsPrevSiblingOf(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Inserts \$node as previous sibling to given destination node \$dest
     *
     * @param   $objectClassName \$dest	Propel object for destination node
     * @param      PropelPDO \$con Connection to use.
     * @return                 $objectClassName The current object (for fluent API support)
     * @throws PropelException - if this object already exists
     */
    public function insertAsPrevSiblingOf(NodeObject \$dest, PropelPDO \$con = null)
    {
        if (!\$this->isNew()) {
            throw new PropelException(\"$objectClassName must be new.\");
        }
        $peerClassname::insertAsPrevSiblingOf(\$this, \$dest, \$con);

        return \$this;
    }
";
    }

    protected function addInsertAsNextSiblingOf(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Inserts \$node as next sibling to given destination node \$dest
     *
     * @param   $objectClassName \$dest	Propel object for destination node
     * @param      PropelPDO \$con Connection to use.
     * @return                 $objectClassName The current object (for fluent API support)
     * @throws PropelException - if this object already exists
     */
    public function insertAsNextSiblingOf(NodeObject \$dest, PropelPDO \$con = null)
    {
        if (!\$this->isNew()) {
            throw new PropelException(\"$objectClassName must be new.\");
        }
        $peerClassname::insertAsNextSiblingOf(\$this, \$dest, \$con);

        return \$this;
    }
";
    }

    protected function addMoveToFirstChildOf(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Moves node to be first child of \$parent
     *
     * @param   $objectClassName \$parent	Propel object for destination node
     * @param      PropelPDO \$con Connection to use.
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function moveToFirstChildOf(NodeObject \$parent, PropelPDO \$con = null)
    {
        if (\$this->isNew()) {
            throw new PropelException(\"$objectClassName must exist in tree.\");
        }
        $peerClassname::moveToFirstChildOf(\$parent, \$this, \$con);

        return \$this;
    }
";
    }

    protected function addMoveToLastChildOf(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Moves node to be last child of \$parent
     *
     * @param   $objectClassName \$parent	Propel object for destination node
     * @param      PropelPDO \$con Connection to use.
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function moveToLastChildOf(NodeObject \$parent, PropelPDO \$con = null)
    {
        if (\$this->isNew()) {
            throw new PropelException(\"$objectClassName must exist in tree.\");
        }
        $peerClassname::moveToLastChildOf(\$parent, \$this, \$con);

        return \$this;
    }
";
    }

    protected function addMoveToPrevSiblingOf(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Moves node to be prev sibling to \$dest
     *
     * @param   $objectClassName \$dest	Propel object for destination node
     * @param      PropelPDO \$con Connection to use.
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function moveToPrevSiblingOf(NodeObject \$dest, PropelPDO \$con = null)
    {
        if (\$this->isNew()) {
            throw new PropelException(\"$objectClassName must exist in tree.\");
        }
        $peerClassname::moveToPrevSiblingOf(\$dest, \$this, \$con);

        return \$this;
    }
";
    }

    protected function addMoveToNextSiblingOf(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Moves node to be next sibling to \$dest
     *
     * @param   $objectClassName \$dest	Propel object for destination node
     * @param      PropelPDO \$con Connection to use.
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function moveToNextSiblingOf(NodeObject \$dest, PropelPDO \$con = null)
    {
        if (\$this->isNew()) {
            throw new PropelException(\"$objectClassName must exist in tree.\");
        }
        $peerClassname::moveToNextSiblingOf(\$dest, \$this, \$con);

        return \$this;
    }
";
    }

    protected function addInsertAsParentOf(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $peerClassname = $this->getStubPeerBuilder()->getClassname();
        $script .= "
    /**
     * Inserts node as parent of given node.
     *
     * @param   $objectClassName \$node Propel object for destination node
     * @param      PropelPDO \$con	Connection to use.
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function insertAsParentOf(NodeObject \$node, PropelPDO \$con = null)
    {
        $peerClassname::insertAsParentOf(\$this, \$node, \$con);

        return \$this;
    }
";
    }

    protected function addGetLeft(&$script)
    {
        $table = $this->getTable();

        foreach ($table->getColumns() as $col) {
            if ($col->isNestedSetLeftKey()) {
                $left_col_getter_name = 'get' . $col->getPhpName();
                break;
            }
        }

        $script .= "
    /**
     * Wraps the getter for the left value
     *
     * @return int
     */
    public function getLeftValue()
    {
        return \$this->$left_col_getter_name();
    }
";
    }

    protected function addGetRight(&$script)
    {
        $table = $this->getTable();

        foreach ($table->getColumns() as $col) {
            if ($col->isNestedSetRightKey()) {
                $right_col_getter_name = 'get' . $col->getPhpName();
                break;
            }
        }

        $script .= "
    /**
     * Wraps the getter for the right value
     *
     * @return int
     */
    public function getRightValue()
    {
        return \$this->$right_col_getter_name();
    }
";
    }

    protected function addGetScopeId(&$script)
    {
        $table = $this->getTable();

        $scope_col_getter_name = null;
        foreach ($table->getColumns() as $col) {
            if ($col->isTreeScopeKey()) {
                $scope_col_getter_name = 'get' . $col->getPhpName();
                break;
            }
        }

        $script .= "
    /**
     * Wraps the getter for the scope value
     *
     * @return int or null if scope is disabled
     */
    public function getScopeIdValue()
    {";
        if ($scope_col_getter_name) {
            $script .= "

        return \$this->$scope_col_getter_name();";
        } else {
            $script .= "

        return null;";
        }
        $script .= "
    }
";
    }

    protected function addSetLeft(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $table = $this->getTable();

        foreach ($table->getColumns() as $col) {
            if ($col->isNestedSetLeftKey()) {
                $left_col_setter_name = 'set' . $col->getPhpName();
                break;
            }
        }

        $script .= "
    /**
     * Set the value left column
     *
     * @param      int \$v new value
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function setLeftValue(\$v)
    {
        \$this->$left_col_setter_name(\$v);

        return \$this;
    }
";
    }

    protected function addSetRight(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $table = $this->getTable();

        foreach ($table->getColumns() as $col) {
            if ($col->isNestedSetRightKey()) {
                $right_col_setter_name = 'set' . $col->getPhpName();
                break;
            }
        }

        $script .= "
    /**
     * Set the value of right column
     *
     * @param      int \$v new value
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function setRightValue(\$v)
    {
        \$this->$right_col_setter_name(\$v);

        return \$this;
    }
";
    }

    protected function addSetScopeId(&$script)
    {
        $objectClassName = $this->getStubObjectBuilder()->getClassname();
        $table = $this->getTable();

        $scope_col_setter_name = null;
        foreach ($table->getColumns() as $col) {
            if ($col->isTreeScopeKey()) {
                $scope_col_setter_name = 'set' . $col->getPhpName();
                break;
            }
        }

        $script .= "
    /**
     * Set the value of scope column
     *
     * @param      int \$v new value
     * @return   $objectClassName The current object (for fluent API support)
     */
    public function setScopeIdValue(\$v)
    {";
        if ($scope_col_setter_name) {
            $script .= "
        \$this->$scope_col_setter_name(\$v);";
        }
        $script .= "

        return \$this;
    }
";
    }
} // PHP5NestedSetBuilder
