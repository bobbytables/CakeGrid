      _____      _           _____      _     _ 
     / ____|    | |         / ____|    (_)   | |
    | |     __ _| | _____  | |  __ _ __ _  __| |
    | |    / _` | |/ / _ \ | | |_ | '__| |/ _` |
    | |___| (_| |   <  __/ | |__| | |  | | (_| |
     \_____\__,_|_|\_\___|  \_____|_|  |_|\__,_|

    Easy tabular data for CakePHP (cakephp.org)
     -- by Robert Ross (rross@sdreader.com)
     -- available at http://github.com/rross0227
<<<<<<< HEAD
     -- requires CakePHP 2.x beta
=======
     -- requires CakePHP 2 Beta
>>>>>>> CakePHP2

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

# Some notes
    CakeGrid was created because we create tables constantly. It's annoying how much we make tables. Especially in our admin.
    Our tables are simple to complex, so columns can use elements, or formatting. Doesn't matter.
    This plugin was created quickly and therefore has no test cases, But is currently in my todo list. 
    <despise>I'll give a cookie to anyone that makes them.</despise>

# How to use

Install the plugin (Read the INSTALL file)

In your controller (or for global usage include it in your AppController) include the helper by adding this line:

    var $helpers = array('CakeGrid.Grid');

In your view file you can now create grids easily by doing something like this:

    $this->Grid->addColumn('Order Id', '/Order/id');
    $this->Grid->addColumn('Order Date', '/Order/created', array('type' => 'date'));
    $this->Grid->addColumn('Order Amount', '/Order/amount', array('type' => 'money'));

    $this->Grid->addAction('Edit', array('controller' => 'orders', 'action' => 'edit'), array('/Order/id'));

    echo $this->Grid->generate($results);
    
This will create a 4 column grid (including actions) for all of your orders or whatever you like!
CakeGrid uses the Set::extract format found here: http://book.cakephp.org/view/1501/extract

If you're generating multiple tables per view, reset the grid and start over after you've generated your result set:

    $this->Grid->reset();
    
# Actions Column

    @param string $name 
    @param array $url 
    @param array $trailingParams
    
    $this->Grid->addAction('Edit', array('controller' => 'orders', 'action' => 'edit'), array('/Order/id'));
    
## What this does:

The First parameter if the link text (Edit, Delete, Rename, etc..)
The Second parameter is the controller action that will be handling the action.
The Third parameter is for the action parameters. So the id of the result, maybe a date? Whatever. Use your imagination.


# Advanced Functionality

CakeGrid allows you to make column results linkable. For example, if a column is for the order number, you can make the result a link to the actual order details.

For example:

    $this->Grid->addColumn('ID', '/Order/id', array('linkable' => array(
    	'url' => array('action' => 'details'),
    	'trailingParams' => array('/Order/id')
    )));
    
Linkable is the option parameter takes 3 sub options. url, trailingParams, and Html::link options (see http://book.cakephp.org/view/1442/link)

The url could be considered the controller and action, and maybe a named parameter. The trailing parameters is the id or whatever you like. It will be pulled from the result.
__Note:__ Named parameters are not yet supported, but so array('named' => array('id' => '/Order/id')) will not work, but array('id' => '/Order/id') will

## Total Row

To create a "totals" row. You can set a column to total. Only money and numbers will work (obviously).

The syntax is as follows:

    $this->addColumn('Amount', '/Order/amount', array('total' => true));
    
This will produce a final row with the totals on it for the column. If the column type is set to money or number, it will format the totals as well.

## Concat and Format

CakeGrid allows you to do concatenation and sprintf formatting on your cells. For example, if you have a first and last name but don't want to use CakePHP's virtualFields to merge them together, you can use CakeGrid to do it.

### Concat

    $this->Grid->addColumn('User', array(
    	'type' => 'concat', 
    	'/User/first_name',
    	'/User/last_name'
    ));
    
This will output in the cell the users first and last name together. Concat uses spaces as the default separator but can be changed in 2 ways.
    
    // Inline with the column options
    $this->Grid->addColumn('User', array(
    	'type' => 'concat', 
    	'separator' => ' ',
    	'/User/first_name',
    	'/User/last_name'
    ));
    
    // Global usage
    $this->Grid->options(array(
        'separator' => ' '
    ));
    
### Formatting

    $this->Grid->addColumn('Register Date', array(
        'type' => 'format',
        'with' => '%s (%s)',
        '/User/created',
        '/User/register_ip'
    ));

## Elements

CakeGrid allows the usage of your own elements to be used in cells. This is useful if you're wanting to use a hasMany relationship into a dropdown or something similar.
When using an element, a valuePath is not used. CakeGrid will pass the entire result of the row to the element.

For Example:

    $this->Grid->addColumn('Purchases', null, array('element' => 'purchase_list'));
    
Whatever the result is for the current row will get passed to the element as $result.

So in your element (purchase_list.ctp for example)

    <?php foreach($result['Purchase'] as $purchase): ?>
    <?php endforeach; ?>
    
