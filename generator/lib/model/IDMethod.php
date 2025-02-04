<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Interface for various ID retrieval method types
 * (i.e. auto-increment, sequence, ID broker, etc.).
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     Daniel Rall <dlr@collab.net> (Torque)
 * @package    propel.generator.model
 */
interface IDMethod
{

    /**
     * Key generation via database-specific ID method
     * (i.e. auto-increment for MySQL, sequence for Oracle, etc.).
     */
    public const NATIVE = "native";

    /**
     * No RDBMS key generation (keys may be generated by the
     * application).
     */
    public const NO_ID_METHOD = "none";
}
