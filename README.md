      _____      _           _____      _     _ 
     / ____|    | |         / ____|    (_)   | |
    | |     __ _| | _____  | |  __ _ __ _  __| |
    | |    / _` | |/ / _ \ | | |_ | '__| |/ _` |
    | |___| (_| |   <  __/ | |__| | |  | | (_| |
     \_____\__,_|_|\_\___|  \_____|_|  |_|\__,_|

Scoreboard for Mockingbird
 -- by Robert Ross (rross@sdreader.com)
 -- available at http://github.com/############
 -- requires CakePHP 1.3.x

Copyright (c) 2011 The Daily Save, LLC.  All rights reserved.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

# How to use

Install the plugin (Read the INSTALL file)

In your controller (or for global usage include it in your AppController) include the helper by adding this line:

    var $helpers = array('CakeGrid.Grid');

In your view file you can now create grids easily by doing something like this:

    $this->Grid->addColumn('Order Id', '/Order/id', array('editable' => array('editKey' => 'id')));
    $this->Grid->addColumn('Order Date', '/Order/created', array('type' => 'date'));
    $this->Grid->addColumn('Order Amount', '/Order/amount', array('type' => 'money'));

    $this->Grid->addAction('Edit', array('controller' => 'orders', 'action' => 'edit'), array('/Order/id'));

    echo $this->Grid->generate($results);
    
This will create a 4 column grid (including actions) for all of your orders or whatever you like!
CakeGrid uses the Set::extract format found here: http://book.cakephp.org/view/1501/extract

If you're generating multiple tables per view, reset the grid and start over after you've generated your result set:

    $this->Grid->reset();