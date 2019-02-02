<?php

namespace Mantis\Tree;

class Tree
{
    /**
     * An array of tree nodes
     *
     * @var Node[]
     */
    protected $nodes = array();

    /**
     * ID of the root nodes
     *
     * @var int Root Node ID
     */
    protected $root;

    /**
     * Tree constructor.
     * @param array $data The data for the tree
     * @param int $root ID of the root node, defaults to 0
     */
    public function __construct(array $data = [], $root = 0)
    {
        $this->root = $root;
        $this->build($data);
    }

    /**
     * Private method to build the tree
     *
     * @param array $data The data containing the tree to generate
     *
     */
    private function build(array $data)
    {
        $children = [];

        // Create the root node
        $this->nodes[$this->root] = $this->createNode( [ 'id' => $this->root ] );

        foreach ($data as $row) {
            if ($row['parent'] === null) {
                $row['parent'] = $this->root;
            }
            $this->nodes[$row['id']] = $this->createNode($row);
            if (empty($children[$row['parent']])) {
                $children[$row['parent']] = [($row['id'])];
            } else {
                $children[$row['parent']][] = $row['id'];
            }
        }

        foreach ($children as $parent_id => $child_ids) {
            foreach ($child_ids as $child_id) {
                if ((string)$parent_id === (string)$child_id) {
                    throw new \RuntimeException("Node (ID $child_id) references itself as parent");
                }
                if (isset($this->nodes[$parent_id])) {
                    $this->nodes[$parent_id]->addChild($this->nodes[$child_id]);
                } else {
                    throw new \RuntimeException("Node with ID $child_id points to non-existent parent with ID $parent_id");
                }
            }
        }
    }

    /**
     * Creates and returns a node with the given properties
     *
     * Can be overridden by subclasses to use a Node subclass for nodes.
     *
     * @param array $data
     *
     * @return Node
     */
    protected function createNode($data)
    {
        return new Node($data);
    }

    /**
     * Returns a flat, sorted array of all node objects in the tree.
     *
     * @return Node[] Nodes, sorted as if the tree was hierarchical, i.e. first level 1 item, then their children..
     */
    public function getNodes()
    {
        $nodes = [];
        foreach ($this->nodes[$this->root]->getDescendants() as $descendant) {
            $nodes[] = $descendant;
        }
        return $nodes;
    }

    /**
     * Returns a single node from the tree by its ID.
     *
     * @param int $id Node ID
     * @throws \InvalidArgumentException
     *
     * @return Node
     */
    public function getNodeById($id)
    {
        if (empty($this->nodes[$id])) {
            throw new \InvalidArgumentException("Invalid node primary key $id");
        }
        return $this->nodes[$id];
    }

    /**
     * Returns an array of all the top level nodes in the tree
     *
     * @return Node[] Nodes in the correct order
     */
    public function getRootNodes()
    {
        return $this->nodes[$this->root]->getChildren();
    }

    /**
     * Returns a textual representation of the tree layout
     *
     * @return string
     */
    public function __toString()
    {
        $str = [];
        foreach ($this->getNodes() as $node) {
            $str[] = str_repeat('  ', $node->getDepth() - 1) .'- ' . (string)$node;
        }
        return implode(PHP_EOL, $str);
    }
}
