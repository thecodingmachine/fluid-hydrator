<?php

namespace FluidHydratorTest;


use MetaHydrator\Exception\HydratingException;
use Mouf\Hydrator\Hydrator;
use TheCodingMachine\FluidHydrator\FluidHydrator;

class FluidHydratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var User */
    static $user;

    /** @var Hydrator */
    private $hydrator;

    protected function setUp()
    {
        $this->hydrator = FluidHydrator::new()
            ->field('firstname')->string()->required()->maxLength(55)->then()
            ->field('lastname')->string()->required()
            ->field('email')->string()->required()->email()
            ->field('phone')->string()->required()->phone()
            ->field('birthdate')->date('d/m/Y')->required()
            ->field('things')->object(Thing::class)
            ->begin()
                ->field('name')->string()->required()
                ->field('price')->int()
                ->field('weight')->int()
                ->field('color')->string()->enum(['red', 'green', 'blue', 'pink', 'black', 'brown', 'white'])->required()
            ->end()->required()->array()->required()
            ->field('address')->subobject(Address::class)
            ->begin()
                ->field('number')->string()
                ->field('street')->string()->required()
                ->field('city')->subobject(City::class)->hydrator()->required()
            ->end()->required()
            ->field('registered')->bool()->required()
            ->field('stuff')->object(Thing::class)->hydrator();
        ;
    }

    public function testCreateFail()
    {

    }

    public function testCreate()
    {
        $data = [
            'firstname' => 'Sherlock',
            'lastname' => 'Holmes',
            'email' => 's.holmes@bakerstreet.en',
            'phone' => '+44(0)6 45 21 78 22',
            'birthdate' => '01/04/1854',
            'registered' => false,
            'address' => [
                'number' => '221 B',
                'street' => 'Baker Street',
                'city' => [
                    'name' => 'Edinburgh',
                    'zipCode' => 'EH1'
                ]
            ],
            'things' => [
                [
                    'name' => 'foo',
                    'color' => 'green',
                ],
                [
                    'name' => 'bar',
                    'color' => 'red'
                ]
            ],
        ];
        try {
            /** @var User $user */
            self::$user = $this->hydrator->hydrateNewObject($data, User::class);
        } catch (HydratingException $exception) {
            $this->assertTrue(false, 'Error hydrating user:'.PHP_EOL.json_encode($exception->getErrorsMap(), JSON_PRETTY_PRINT));
        }
    }

    /**
     * @expectedException \MetaHydrator\Exception\HydratingException
     */
    public function testEditFail()
    {
        $user = clone self::$user;
        $data = [
            'lastname' => 'Savina',
            'things' => [
                [
                    'name' => 'foo',
                    'color' => 'magenta'
                ]
            ],
            'address' => null
        ];

        try {
            $this->hydrator->hydrateObject($data, $user);
        } catch (HydratingException $exception) {
            $errors = $exception->getErrorsMap();
            $this->assertArrayHasKey('address', $errors);
            $this->assertEquals('This field is required', $errors['address']);

            $this->assertArrayHasKey('things', $errors);
            $this->assertArrayHasKey(0, $errors['things']);
            $this->assertArrayHasKey('color', $errors['things'][0]);
            $this->assertArrayHasKey('color', $errors['things'][0]);
            $this->assertEquals('Invalid value', $errors['things'][0]['color']);

            throw $exception;
        }
    }

    public function testEdit()
    {
        $user = clone self::$user;
        $data = [
            'firstname' => 'Dorian',
            'lastname' => 'Savina',
            'things' => [
                [
                    'name' => 'foo',
                    'color' => 'green'
                ]
            ],
            'address' => [
                'city' => [
                    'name' => 'Glasgow',
                ]
            ]
        ];
        try {
            /** @var User $user */
            $this->hydrator->hydrateObject($data, $user);
        } catch (HydratingException $exception) {
            $this->assertTrue(false, json_encode($exception->getErrorsMap(), JSON_PRETTY_PRINT));
        }
        $this->assertEquals('Dorian', $user->getFirstname());
        $this->assertEquals('Savina', $user->getLastname());
        $this->assertEquals('s.holmes@bakerstreet.en', $user->getEmail());
        $this->assertEquals('EH1', $user->getAddress()->getCity()->getZipCode());
    }
}

class User
{
    /** @var string */
    public $firstname;
    public function getFirstname() { return $this->firstname; }
    public function setFirstname($firstname) { $this->firstname = $firstname; }

    /** @var string */
    public $lastname;
    public function getLastname() { return $this->lastname; }
    public function setLastname($lastname) { $this->lastname = $lastname; }

    /** @var Address */
    public $address;
    public function getAddress() { return $this->address; }
    public function setAddress($address) { $this->address = $address; }

    /** @var string */
    public $email;
    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    /** @var string */
    public $phone;
    public function getPhone() { return $this->phone; }
    public function setPhone($phone) { $this->phone = $phone; }

    /** @var \DateTime */
    public $birthdate;
    public function getBirthdate() { return $this->birthdate; }
    public function setBirthdate($birthdate) { $this->birthdate = $birthdate; }

    /** @var Thing[] */
    public $things;
    public function getThings() { return $this->things; }
    public function setThings($things) { $this->things = $things; }

    /** @var Thing */
    public $stuff;
    public function getStuff() { return $this->stuff; }
    public function setStuff($thing) { $this->stuff = $thing; }

    /** @var bool */
    public $registered;
    public function getRegistered() { return $this->registered; }
    public function setRegistered($registered) { $this->registered = $registered; }
}

class Address
{
    /** @var string */
    public $number;
    public function getNumber() { return $this->number; }
    public function setNumber($number) { $this->number = $number; }

    /** @var string */
    public $street;
    public function getStreet() { return $this->street; }
    public function setStreet($street) { $this->street = $street; }

    /** @var City */
    public $city;
    public function getCity() { return $this->city; }
    public function setCity($city) { $this->city = $city; }
}

class City
{
    /** @var string */
    public $name;
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }

    /** @var string */
    public $zipCode;
    public function getZipCode() { return $this->zipCode; }
    public function setZipCode($zipCode) { $this->zipCode = $zipCode; }
}

class Thing
{
    /** @var string */
    public $name;
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }

    /** @var int */
    public $price;
    public function getPrice() { return $this->price; }
    public function setPrice($price) { $this->price = $price; }

    /** @var int */
    public $weight;
    public function getWeight() { return $this->weight; }
    public function setWeight($weight) { $this->weight = $weight; }

    /** @var string */
    public $color;
    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }
}
