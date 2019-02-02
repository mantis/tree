<?php

namespace Mantis\Tree;

/**
 * Class Node
 * @package Mantis\Tree
 */
class Node
{
    /**
     * This is an array of data associated with a node.
     *
     * It will contain:
     * - a mandatory "id" key, which stores the id of the node.
     * - an optional "parent" id will exist if the node is a child
     * - data stored by the user linked to the tree node
     *
     * @var array
     */
    protected $data;

    /**
     * A Node Reference to the parent node, or null for a root node
     *
     * @var Node
     */
    protected $parent = null;

    /**
     * An array of child nodes to the given note
     *
     * The array is indexed so that nodes are in order
     *
     * @var Node[]
     */
    protected $children = array();

    /**
     * Node constructor.
     *
     * @param array $data Array of property data for the node. see $data variable for definition
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    //////////////////////////////////////////////
    // Getter functions for the node properties //
    //////////////////////////////////////////////

    /**
     * Returns the ID of the current node.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->data['id'];
    }

    /**
     * Return data array for the node.
     *
     * This is an array data created with the node, along with it's id and parent id
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the parent node object, or null if there is no parent.
     *
     * @return Node|null Parent node, or if no parent exists, then null.
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns all direct children of this node
     *
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Magic Getter method
     *
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ('parent' === $name || 'children' === $name || 'id' === $name || 'data' === $name) {
            return $this->$name;
        }
        throw new \RuntimeException("Undefined Property: $name (Node: ".$this->data['id'].')');
    }

    /**
     * Magic IsSet method
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return ('parent' === $name || 'children' === $name || 'id' === $name || 'data' === $name);
    }

    /**
     * Returns a textual representation of this node
     *
     * @return string The node's ID
     */
    public function __toString()
    {
        return (string)$this->data['id'];
    }

    /**
     * Returns the depth of this node from the root of the tree
     *
     * @return int Depth of this node in the tree ( 1 = top level )
     */
    public function getDepth()
    {
        if ($this->parent === null) {
            return 0;
        }
        return $this->parent->getDepth() + 1;
    }

    /////////////////////////////////////////////////////////////////////
    // Sibling functions to handle nodes at the same level in the tree //
    /////////////////////////////////////////////////////////////////////

    /**
     * Returns previous node in the same level, or null if first sibling
     *
     * @return Node|null
     */
    public function getPreviousSibling()
    {
        return $this->getSibling(-1);
    }

    /**
     * Returns following node in the same level, or null if last sibling
     *
     * @return Node|null
     */
    public function getNextSibling()
    {
        return $this->getSibling(1);
    }

    /**
     * Returns the sibling with the given offset from this node,
     * or null if there is no such sibling
     *
     * @param int $offset If 1, the next node is returned, if -1, the previous. Can be called with other positional values e.g. +2/-2.
     *
     * @return Node|null
     */
    private function getSibling($offset)
    {
        $nodes = $this->parent->getChildren();
        $position = array_search($this, $nodes);
        if (isset($nodes[$position + $offset])) {
            return $nodes[$position + $offset];
        }
        return null;
    }

    /**
     * Returns siblings of the node
     *
     * @return Node[]
     */
    public function getSiblings()
    {
        $siblings = array();
        foreach ($this->parent->getChildren() as $child) {
            if ((string)$child->getId() !== (string)$this->getId()) {
                $siblings[] = $child;
            }
        }
        return $siblings;
    }

    /**
     * Returns siblings of the node including the node itself.
     *
     * @return Node[]
     */
    public function getSiblingsAndSelf()
    {
        $siblings = array();
        foreach ($this->parent->getChildren() as $child) {
            $siblings[] = $child;
        }
        return $siblings;
    }

    /////////////////////////////////////////////////////////////////////////////
    // Children functions to handle nodes directly below this node in the tree //
    /////////////////////////////////////////////////////////////////////////////

    /**
     * Adds the given node to this node's children
     *
     * @param Node $child
     */
    public function addChild(Node $child)
    {
        $this->children[]            = $child;
        $child->parent               = $this;
    }

    /**
     * Returns whether or not this node has any children
     *
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->children) ? true : false;
    }

    /**
     * Returns number of children this node has
     *
     * @return int
     */
    public function countChildren()
    {
        return count($this->children);
    }

    ///////////////////////////////////////////////////////////////////////////////
    // Nested functions to handle any nodes above or below this node in the tree //
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Returns any node below the current node. This will return children, grandchildren etc
     *
     * This is ordered as per the following example:
     * - A
     *  - 1
     *  - 2
     * - B
     *  - 1
     *  - 2
     *
     * So nodes will be returned as follows:
     *
     * A, A1, A2, B, B1, B2
     *
     * @return Node[]
     */
    public function getDescendants()
    {
        $descendants = array();
        foreach ($this->children as $child) {
            $descendants[] = $child;
            if ($child->hasChildren()) {
                $descendants = array_merge($descendants, $child->getDescendants());
            }
        }
        return $descendants;
    }

    /**
     * Returns the current node and all nodes below it. This will return children, grandchildren etc
     *
     * This is ordered as per the following example:
     * - A
     *  - 1
     *  - 2
     * - B
     *  - 1
     *  - 2
     *
     * So nodes will be returned as follows:
     *
     * A, A1, A2, B, B1, B2
     *
     * @return Node[]
     */
    public function getDescendantsAndSelf()
    {
        return array_merge(array($this), $this->getDescendants());
    }

    /**
     * Returns any node above the current node. This will return parent, grandparent etc
     *
     * The array returned from this method will include the root node (array_pop() can be used to remove this if required).
     *
     * @return Node[] Indexed array of nodes, sorted from the nearest (self) to the furthest (parent, grandparent etc)
     */
    public function getAncestors()
    {
        $ancestors = array();

        if (null === $this->parent) {
            return $ancestors;
        }

        return array_merge($ancestors, $this->parent->getAncestorsAndSelf());
    }

    /**
     * Returns the current node and any node above the current node. This will return parent, grandparent etc
     *
     * Note: The array returned from this method will include the root node. If you
     * do not want the root node, you should do an array_pop() on the array.
     *
     * @return Node[] Indexed array of nodes, sorted as self, parent, grandparent etc
     */
    public function getAncestorsAndSelf()
    {
        return array_merge(array($this), $this->getAncestors());
    }
}
