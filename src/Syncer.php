#!/usr/bin/env php
<?php
/**
 * Joomla! GitHub API syncer.
 *
 * @copyright  Copyright (C) 2016 Nikolai Plath - elkuku.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Application;

use ElKuKu\Syncer\Application;

include '../vendor/autoload.php';

(new Application)
	->execute();
