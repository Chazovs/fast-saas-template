<?php

namespace App\Exception;

use Doctrine\ORM\Exception\RepositoryException;
use Exception;

class AccessException extends Exception implements RepositoryException
{
}
