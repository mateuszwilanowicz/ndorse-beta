<?php

	/**
	 * A class that wraps PHP's array functionality and provides convenient access
	 *
	 */
	class Map implements Iterator {

		private $items = array();


		/**
		 * Empty constuctor
		 */
		public function __construct() {
		}

		/**
		 * Do not mess with rewind, current, key, next and valid.
		 * These methods are required for the Iterator interface.
		 */

		public function rewind() {
	        reset($this->items);
	    }

	    public function current() {
	        return current($this->items);
	    }

	    public function key() {
	        return key($this->items);
	    }

	    public function next() {
	        return next($this->items);
	    }

	    public function valid() {
	        return $this->current() !== false;
	    }

		/**
		 * Adds an object to the map with the key that is passed in.
		 * If an entry with the same key exists it will NOT overwrite it.
		 */
		public function add($key, $object, $replace = false) {
			if (!$this->contains($key) || $replace) {
			    $this->items[$key] = $object;
			} else {
			    $i = 1;
			    while($this->contains($key.$i))
                    $i++;
			    $this->items[$key.$i] = $object;
			}

		}


		public function addAll(Map $map) {
			$this->items = array_merge($this->items, $map->getContents());
			return $this;
		}


		/**
		 * Remove an object and key from the map based upon the key passed in.
		 */
		public function remove($key) {
			unset($this->items[$key]);
		}


		/**
		 * Retrieves an item with the matching key.
		 */
		public function get($key) {
			if ($this->contains($key))
				return $this->items[$key];
			else
				return null;
		}

		/**
		 * Magic function for returning map elements.
		 */
		public function __get($property) {
			$key = strtoupper($property);
			if ($this->contains($key))
				return $this->items[$key];
			else
				return null;
		}

		/**
		 * Returns a bool indicating whether or not the key exists in the map.
		 */
		public function contains($key) {
			return array_key_exists($key, $this->items);
		}


		/**
		 * Clear the entire map.
		 */
		public function clear() {
			$this->items = array();
		}


		/**
		 * Return the count of the elements in the map.
		 */
		public function getCount() {
			return sizeof($this->items);
		}


		public function getFirst() {
			foreach ($this->items as $item)
				return $item;
		}


		public function toNumericArray() {
			return array_values($this->items);
		}

		/**
		 * Returns the keys for the items in the Map.
		 */
		public function getKeys() {
			return array_keys($this->items);
		}

		public function getContents() {
			return $this->items;
		}

		public function setContents($contents) {
			$this->items = $contents;
		}

		public function elementAt($index) {
			$x = 0;
			foreach ($this->items as $item) {
				if ($x == $index)
					return $item;
				$x++;
			}
			return null;
		}

		// methods

		public function sort($criteria = null) {
			if ($this->getCount() > 1) {
				if ($criteria != null) {
					// We use uasort not usort in order to preserve the keys
					// TODO: work out why this is throwing a warning
					// it is not throwing a warning
					@uasort($this->items, $criteria);
				} else {
					sort($this->items);
				}
			}
			return $this;
		}

		public function reverse() {
			$this->items = array_reverse($this->items, true);
		}

		public function subset($start, $length) {
			// zero indexed
			if (!isset($start)) {
				return $this->items;
			} else {
				return array_slice($this->items, $start, $length);
			}
		}

		public function page($pageSize, $pageNum) {
			$start = ($pageNum - 1) * $pageSize;
			return $this->subset($start, $pageSize);
		}

		public function toArray() {
			$items = array();
			foreach ($this->items as $key => $item) {
				$items[$key] = $item->toArray();
			}
			return $items;
		}

		public function toXML($xmlparent, $elementName = 'Map', $forexport = false) {

			$xmlmap = $xmlparent->addChild($elementName);

			foreach ($this->items as $item) {
				if (method_exists($item, 'toXML')) {
					$item->toXML($xmlmap, false, $forexport);
				}
			}

			return $xmlmap;
		}

	}

?>