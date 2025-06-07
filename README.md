<h1 style="dislay=flex;">Laravel E-Commerce with React-TS

<span>
    
![larastore_logo_preview](https://github.com/user-attachments/assets/b794851a-3388-4152-bae6-4e67f81ed48f)

</span>
</h1>

## This project was built on Laravel ( 11.41.3 ) with ReactJS, Typscript, TailwindCSS, InertiaJS, DaisyUI, and Filament.

The purpose of this project was to build a web application combining Laravel with ReactJS along with TypeScript. An Amazon inspired E-Commerce website using Single-Page Application (SPA) with Server-Side Rendering (SSR).

SSR was chosen to be able to add SEO.

Stripe is used as the online payment platform to handle payment processses.

The project utilizes SQLite, a file-based database, and Laravel Herd, for the Nginx, web-server.

The project was developed using PHP version 8.3.


## Locally running the Application

## Start Laravel Herd (Free Ver.)

- Install and move the project to Laravel Herd project folder.

- Ensure that the PHP version matches the one used in this project.

### Starting Vite Development Server

```
npm run dev
```

### Starting Mailpit

Instructions to setup mailpit can be accessed through this link: https://drive.google.com/drive/folders/1r0iyesRGNJBIpvWeP4jMXLuqltZFO23I?usp=sharing

Ensure that you have setup Mailpit before running the command below.

```
mailpit
```

### Starting Stripe Webhook

Instructions to setup mailpit can be accessed through this link: https://drive.google.com/drive/folders/1jhECj03pi_6RspdygJuFmxqrMZMoyJHE?usp=sharing

Ensure that you have setup a Stripe account for testing purposes and its PHP package before running the command below.

```
stripe listen --forward-to laravel-react-ts-ecommerce.test/stripe/webhook
```

## Web-Application Sections

The project consist of the following sections.

* __Larastore Homepage__\
  |\
  |-------> __Home__ (List all products)\
  |\
  |-------> __Product Category__ (Choose which product category on the sub-navigation on the navbar)\
  |\
  |-------> __Show Product__ (User can add the product to cart)\
  |\
  |-------> __Vendor Profile__ (Vendor store page)\
  |\
  |-------> __My Orders__ (Show the orders of the user that have/had been placed)\
  |\
  |-------> __Cart__ (Show the user's cart items)
  |\
  |-------> __Profile__ (Edit user information and apply for vendor)
  
* __Filament Dashboard__\
  |\
  |-------> __Admin Dashboard__\
  |           &emsp;&emsp;&emsp;&emsp;&emsp; |\
  |           &emsp;&emsp;&emsp;&emsp;&emsp; |-------> __Dashboard__\
  |           &emsp;&emsp;&emsp;&emsp;&emsp; |\
  |           &emsp;&emsp;&emsp;&emsp;&emsp; |-------> __Departments__\
  |           &emsp;&emsp;&emsp;&emsp;&emsp; |\
  |           &emsp;&emsp;&emsp;&emsp;&emsp; |-------> __Users__\
  |           &emsp;&emsp;&emsp;&emsp;&emsp; |\
  |           &emsp;&emsp;&emsp;&emsp;&emsp; |-------> __Vendors__\
  |\
  |-------> __Vendor Dashboard__\
             &emsp;&emsp;&emsp;&emsp;&emsp; |\
             &emsp;&emsp;&emsp;&emsp;&emsp; |-------> __Dashboard__\
             &emsp;&emsp;&emsp;&emsp;&emsp; |\
             &emsp;&emsp;&emsp;&emsp;&emsp; |-------> __Products__\
             &emsp;&emsp;&emsp;&emsp;&emsp; |\
             &emsp;&emsp;&emsp;&emsp;&emsp; |-------> __Orders__\

## Roles

* Customer User
* Vendor User
* Admin User

## Testing

Feature tests were performed using __PHPUnit__. Simple tests were made for authentication, authorization, accessible page, see a particular text, ensure persisted or deleted database records, and stored uploaded files. These tests were tested to run as individual classes and as a whole.

The point of testing in general is to be confident that the project functions properly. Consequently, testing is very helpful in ensuring that the codebase works as expected, and in identifying and fixing any code functionality that was overlooked during development.

## Future Improvements

- [ ] :x: Display order details using the 'address' data within order item from the 'My Orders' page.

- [ ] :x: Display invoice through a pdf view from the 'My Orders' page.

- [ ] :x: Apply 'Load when visible' functionality provided by InertiaJS for the 'My Orders' page.

- [ ] :x: Implement a date filter on the 'My Orders' page using the filter from Amazon's user's orders page.

- [ ] :x: Show content on the 'Cancelled' and 'Archived' tabs of the 'My Orders' page.

- [ ] :x: Implement autofill for order address when the user click the autofill button before confirming checkout.

## Third-Party Libraries

These libraries were installed (with composer and NPM) to provide additional functionalities either to simplify creating web components or expand existing features. The following lists out the third-party libraries used in this project.

__NPM__

* DaisyUI (4.12.23)
* InertiaJS (2.0.0)
* ReactJS (18.2.08)
* TailwindCSS (3.4.17)
* Typescript (5.0.2)
* Vite (6.0.11)

__Composer__

* Filament (3.2.137)
* Pexels (1.1.4)
* Filament Spatie Media Library (3.2.115)
* Laravel Stripe Connect (0.5.0)
* Laravel Permission (6.13.0)
* Stripe PHP (16.6.0)
* Ziggy (2.5.1)

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
