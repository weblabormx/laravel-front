# Front

_This package was inspired on Laravel Nova._

Front is a administration panel for Laravel. It allows you to create CRUD easily in minutes. It allows to fully customize any part of the code.

*Differences with Laravel Nova:* 

- Front is created just with PHP, so there isn't any dependency with any javascript framework.
- It tries to be simpler to modify and adapt to any use.
- Menu is not generated automatically

## Documentation
### Installation
The easiest way to install Laravel Front is using the [base-laravel-front project](https://github.com/weblabormx/base-laravel-front) as a base. But in case you want to install manually you can follow the next instructions.

- Install via composer executing `composer require weblabormx/laravel-front`
- Execute `php artisan front:install` to install all necessary files (Will install configuration file and views)

_Adapt the layout_
- Add on your layout the code `@yield('after-nav')` as there is where Laravel Front shows the menu
- Add on your layout the code `@yield('content')` as there is where Laravel Front shows the content of the cruds
- Add on your layout the code `@yield('sidebar')` as there is where Laravel Front shows the filters on the menu
- Add on your layout the code `@stack('scripts-footer')` as there is where Laravel Front shows the javascript info
- Add on the layout the next code `@include('flash::message')`, this will show the success and errors messages
- This package requires of jquery, so please be sure that its called before executing `@yield('scripts-footer')`
- You can edit anything on the views published on `views/vendor/front` to adapt to your previous desing.
- Edit the config.front to select the correct layout

#### Layout example
```php
@yield('after-nav')
<main class="container-fluid container">
    @if(View::hasSection('sidebar') && strlen(trim(View::getSections()['sidebar']))>0)
        <div class="row">
            <div class="col-sm-3 py-4">
                @yield('sidebar')
            </div>
            <div class="col-sm-9 py-4">
                @include('flash::message')
                @yield('content')
            </div>
        </div>
    @else
        <div class="py-4">
            @include('flash::message')
            @yield('content')
        </div>
    @endif
</main>

<script
    src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
    crossorigin="anonymous"></script>
@stack('scripts-footer')
```

### Basics
Laravel Front makes a use of different items that can be defined on the next way:

- **Front Resources:** Basically is the front end information about a model and that is wanted to have a CRUD. The resources saves information about which fields show on the cruds.
- **Actions:** Are actions that can be added to a resource, for example can be a resource for Reservations and there can be an action for "Mark as Paid" 
- **Filters:** Filters works for filtering the data on the index page of a crud, for example for Reservations page you can filter the results to only show payed reservations
- **Fields:** The resources can have fields to show information
    - _Inputs:_ Are all the fields that add information as Text, Number, Select
    - _Texts:_ Are fields that only shows information as Alert, Title
    - _Components:_ Are more sophisticated fields that help on something like Panels, Line, FrontIndex
    - _Workers_:_ Are helpers that executes something, this always return a value.
- **Massives:** Define how a massive edition should work. For example you can have the users CRUD, each user have a lot of reservations, and you want to edit massively all reservations for this user, you can add more information on this classes, for example, adding a new button that says "Remove all" or "Send request"
- **Pages:** Are information pages that only shows information, for example the Dashboard page
- **Cards:** Are information cards that can be shown on pages, for example: The total money earned today, The total debt, etc
- **Lenses:** Diferent views for an index page for a CRUD, for example Products can have different views or reports, one in general, other showing and ordered by most selled, etc.

### Resources

A resource is a class that allows you to configure all the information about the front end of a Laravel Model. Things as fields, url, buttons and more are configured on this file.

#### Registering resources

The front resources are saved by default on `App\Front` folder. You can generate a new front using the artisan command (In this case a new resource alled Page):

```
php artisan front:resource Page
```

A new file on `App\Front\` will be added, you need to configure there the fields, base url and model direction.

And then you will need to add the route. This will generate a access on `/pages` (It uses the plural name)

```php
Route::front('Page');
```

The front resources needs to have a Policy name, so please create a Policy for the model and be sure that are defined on the `AuthServiceProvider` (Just if you are using different folder for models as Laravel detects it automatically)

#### Working with resources

There are some basic variables that can be added on the resource

```php
public $title;      // Field name of the title (Name is the default value)
public $model;      // Direction of the model
public $base_url;   // Url created on routes (Required)
public $actions = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']; // If you want to modify which actions to be available
```

#### Modifying query of results

If you want to modify the results of CRUD you are able to modify it with `indexQuery` function

```php
public function indexQuery($query)
{
    return $query->where('team_id', 1)->with(['user'])->latest();
}
```

#### Pagination

By default a pagination is created with 50 elements, if you want to modify the quantity you can add a new attribute called `pagination` on the resource

```php
public $pagination = 50;
```

#### Actions to be executed after a crud action is done

You can add to any resource some actions to be done after something is done adding the next functions on the Front File

- `index()`
- `show($object)`
- `store($object, $request)`
- `update($object, $request)`
- `destroy($object)`

### Fields

Each resource contains a `fields` method, where it returns an array with the list of all the fields that the resource should have.

```php
use WeblaborMx\Front\Inputs\ID;
use WeblaborMx\Front\Inputs\Text;
use WeblaborMx\Front\Inputs\HasMany;
use WeblaborMx\Front\Inputs\Date;
use WeblaborMx\Front\Inputs\Boolean;

public function fields()
{
    return [
        ID::make(),
        Text::make('Name')->rules('required'),
        Text::make('Email')->rules(['email', 'required']),
        Text::make('Telephone')->rules('required'),
        Boolean::make('One Off Is Active')->default(true),
        Boolean::make('Recovery Is Active')->default(true),
        Date::make('Updated At')->exceptOnForms(),
        HasMany::make('Reservation'),
        HasMany::make('ClientSearch'),
    ];
}
```

#### Field column convention

As noted above, Front will "snake case" the displayable name of the field to determine the underlying database column. However, if necessary, you may pass the column name as the second argument to the field's make method:

```php
Text::make('Name', 'name_column')
```

#### Showing / Hidding Fields

- hideFromIndex
- hideFromDetail
- hideWhenCreating
- hideWhenUpdating
- onlyOnIndex
- onlyOnDetail
- onlyOnForms
- onlyOnEdit
- onlyOnCreate
- exceptOnForms

You may chain any of these methods onto your field's definition in order to instruct Frpmt where the field should be displayed:

```php
Text::make('Name')->hideFromIndex()
```

#### Field Panels

If your resource contains many fields, your resource "detail" screen can become crowded. For that reason, you may choose to break up groups of fields into their own "panels":

You may do this by creating a new Panel instance within the fields method of a resource. Each panel requires a name and an array of fields that belong to that panel:

```php
use WeblaborMx\Front\Inputs\ID;
use WeblaborMx\Front\Inputs\Text;
use WeblaborMx\Front\Components\Panel;

public function fields()
{
    return [
        Panel::make('General Information', $this->generalInformationFields()),
    ];
}

public function generalInformationFields()
{
    return [
        ID::make('ID'),
        Text::make('Code')->exceptOnForms(),
        Text::make('Name')->rules('required'),
    ];
}
```

#### Field types

All the fields available on front:

- Autocomplete
- BelongsTo
- BelongsToMany
- Boolean
- Check
- Checkboxes
- Code
- Date
- DateTime
- Disabled
- File
- HasMany
- Hidden
- ID
- Image
- Input
- Money
- MorphMany
- MorphTo
- MorphToMany
- Number
- Password
- Select
- Text
- Textarea
- Time
- Trix

#### Text types

- Alert
- Heading
- HorizontalDescription
- Paragraph
- Table
- Title

#### Components

- FrontCreate
- FrontIndex
- Line
- Panel
- ShowCards
- Welcome


#### Massive editions

If you want to a relationship resource to be edited massively just add `enableMassive()` function.

```php
HasMany::make('Reservation')->enableMassive(),
```
### Filters

You can add filters to a resource to filter the data, the filters are stored on `App\Front\Filters` folder. You can add filters by executing the command `php artisan front:filter FilterName`

Once you execute the command by first time the folder will be generated and the filter `SearchFilter` will be added automatically.

Once is created you need to add it on the resource page

```php 
public function filters()
{
    return [
        new SearchFilter,
    ];
}
```

#### Adding a default value

You can add a default value to the filter by adding the attribute default 

```php
class ActiveFilter extends Filter
{
    public $default = 1;
}
```

### Actions

You can add actions buttons to any resource, so for example if you want to resend email for a Reservation you can create a new action that will resend the email.

Just create a action on `App\Front\Actions`, the structure is similar to the next file

```php
namespace App\Front\Actions;

use WeblaborMx\Front\Inputs\Text;
use Illuminate\Http\Request;

class ResendEmail extends Action
{
    public function handle($object, Request $request)
    {
        // Execute what you want to do
    }

    public function fields()
    {
        // Do you need to ask some information? You can avoid this function if just want to execute an action
        return [
            Text::make('Note')->rules('required'),
        ];
    }
}
```

Then add on the front resource (Reservation on this case) the next function

```php
public function actions()
{
    return [
        new ResendEmail
    ];
}
```

### Pages
You can create pages on the system, on the routes you need to add it easily with `Route::page('PageName', '/');` and execute the command `php artisan front:page PageName`

You will able to change the data on `app/Front/PageName`

### Lenses
To create lenses just create the class on `app/Front/Lenses/` folder. The lenses have the next structure:

```php
namespace App\Front\Lenses;

use WeblaborMx\Front\Traits\IsALense;
use WeblaborMx\Front\Inputs\Date;
use WeblaborMx\Front\Inputs\Number;
use WeblaborMx\Front\Inputs\Money;
use App\Front\Fuel;

class FuelByDate extends Fuel
{
    use IsALense; // Required

    public $lense_title = 'By Date'; // Title of the lense

    public function fields()
    {
        return [
            Date::make('Date'),
            Number::make('Quantity', 'quantity_sum'),
            Money::make('Price', 'price_sum'),
        ];
    }
}

```

Then you need to add the lense to the resource (In this case, the Fuel Front File)

```php
namespace App\Front;

use App\Front\Resource;
use App\Front\Lenses\FuelByDate;

class Fuel extends Resource
{
    public function lenses()
    {
        return [
            new FuelByDate
        ];
    }
}

```

The lenses have the same functionality as the Front Resources, so you customize fully the way it works.

## Customizing the theme

### Sidebar

You can customize the sidebar of the Front Panel editing the file on `resources/front/sidebar`


## Premium Support
If you'd like to implement this package in your project and need our help, or you just want to help this package to continue being develop please write us to carlosescobar@weblabor.mx and we can talk about prices for premium support.
