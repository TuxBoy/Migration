<?php
namespace TuxBoy\Annotation;

/**
 * AnnotationsName
 *
 * Annotation list for the migration system in the entity
 */
abstract class AnnotationsName
{
    /**
     * Value annotation
     *
     * @var string|boolean
     */
    const P_VAR = 'var';

    /**
     * Boolean annotation
     *
     * @text
     */
    const P_TEXT = 'text';

    /**
     * Integer annotation value
     *
     * @length 60
     */
    const P_LENGTH = 'length';

    /**
     * Text value annotation
     *
     * @storeName table_name
     */
    const C_STORE_NAME = 'storeName';

}