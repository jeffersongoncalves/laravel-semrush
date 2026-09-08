<?php

namespace JeffersonGoncalves\Semrush\Exceptions;

use RuntimeException;

/**
 * Thrown when Semrush answers HTTP 200 with an `ERROR ...` body — its way of
 * reporting an invalid key, an unknown database, or an empty report.
 */
class SemrushException extends RuntimeException {}
