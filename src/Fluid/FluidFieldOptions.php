<?php

namespace TheCodingMachine\FluidHydrator\Fluid;

use MetaHydrator\Handler\HydratingHandlerInterface;
use MetaHydrator\Handler\SimpleHydratingHandler;
use MetaHydrator\Handler\SubHydratingHandler;
use MetaHydrator\Parser\ArrayParser;
use MetaHydrator\Validator\EnumValidator;
use MetaHydrator\Validator\MaxLengthValidator;
use MetaHydrator\Validator\NotEmptyValidator;
use MetaHydrator\Validator\RegexValidator;
use MetaHydrator\Validator\ValidatorInterface;
use Mouf\Hydrator\Hydrator;
use TheCodingMachine\FluidHydrator\FluidHydrator;

class FluidFieldOptions implements Hydrator
{
    const EMAIL_REGEX = '/^[a-zA-z0-9.-]+\\@[a-zA-z0-9.-]+.[a-zA-Z]+$/';
    const PHONE_REGEX = '/^(0|\\+(011|999|998|997|996|995|994|993|992|991|990|979|978|977|976|975|974|973|972|971|970|969|968|967|966|965|964|963|962|961|960|899|898|897|896|895|894|893|892|891|890|889|888|887|886|885|884|883|882|881|880|879|878|877|876|875|874|873|872|871|870|859|858|857|856|855|854|853|852|851|850|839|838|837|836|835|834|833|832|831|830|809|808|807|806|805|804|803|802|801|800|699|698|697|696|695|694|693|692|691|690|689|688|687|686|685|684|683|682|681|680|679|678|677|676|675|674|673|672|671|670|599|598|597|596|595|594|593|592|591|590|509|508|507|506|505|504|503|502|501|500|429|428|427|426|425|424|423|422|421|420|389|388|387|386|385|384|383|382|381|380|379|378|377|376|375|374|373|372|371|370|359|358|357|356|355|354|353|352|351|350|299|298|297|296|295|294|293|292|291|290|289|288|287|286|285|284|283|282|281|280|269|268|267|266|265|264|263|262|261|260|259|258|257|256|255|254|253|252|251|250|249|248|247|246|245|244|243|242|241|240|239|238|237|236|235|234|233|232|231|230|229|228|227|226|225|224|223|222|221|220|219|218|217|216|215|214|213|212|211|210|98|95|94|93|92|91|90|86|84|82|81|66|65|64|63|62|61|60|58|57|56|55|54|53|52|51|49|48|47|46|45|44|43|41|40|39|36|34|33|32|31|30|27|20|7|1)( ?\\(0\\))? ?)[1-9]([-. ]?[0-9]{2}){4}$/';

    /**
     * @var FluidHydrator
     */
    private $hydrator;
    /**
     * @var SimpleHydratingHandler|SubHydratingHandler
     */
    private $handler;

    /**
     * FluidFieldOptions constructor.
     * @param $hydrator
     * @param SimpleHydratingHandler|SubHydratingHandler $handler
     */
    public function __construct($hydrator, $handler)
    {
        $this->hydrator = $hydrator;
        $this->handler = $handler;
    }

    /**
     * @param mixed $value
     * @return FluidFieldOptions
     */
    public function default($defaultValue): FluidFieldOptions
    {
        $this->handler->setDefaultValue($defaultValue);
        return $this;
    }

    /**
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function required(string $errorMessage = 'This field is required'): FluidFieldOptions
    {
        return $this->validator(new NotEmptyValidator($errorMessage));
    }

    /**
     * @param string $pattern
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function regex(string $pattern, string $errorMessage = 'Invalid value'): FluidFieldOptions
    {
        return $this->validator(new RegexValidator($pattern, $errorMessage));
    }

    /**
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function email(string $errorMessage = 'Invalid email'): FluidFieldOptions
    {
        return $this->regex(self::EMAIL_REGEX, $errorMessage);
    }

    /**
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function phone(string $errorMessage = 'Invalid phone number'): FluidFieldOptions
    {
        return $this->regex(self::PHONE_REGEX, $errorMessage);
    }

    /**
     * @param array $values
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function enum(array $values, string $errorMessage = 'Invalid value'): FluidFieldOptions
    {
        return $this->validator(new EnumValidator($values, $errorMessage));
    }

    /**
     * @param int $maxLength
     * @param string $errorMessage
     * @return FluidFieldOptions
     */
    public function maxLength(int $maxLength, string $errorMessage = 'Invalid value'): FluidFieldOptions
    {
        return $this->validator(new MaxLengthValidator($maxLength, $errorMessage));
    }

    /**
     * @return FluidFieldOptions
     */
    public function array(string $errorMessage = 'Invalid value'): FluidFieldOptions
    {
        if ($this->handler instanceof SimpleHydratingHandler) {
            $parser = $this->handler->getParser();
            $validators = $this->handler->getValidators();
            $this->handler->setParser(new ArrayParser($parser, $validators, $errorMessage));
            $this->handler->setValidators([]);
        } elseif ($this->handler instanceof SubHydratingHandler) {
            // TODO: replace SubHydratingHandler with a SubArrayHydratingHandler when available
            throw new \Exception('Error: array() not supported on a subField');
        } else {
            throw new \Exception('Error: array() not supported on handler of type '. get_class($this->handler));
        }
        return $this;
    }

    /**
     * @param ValidatorInterface $validator
     * @return FluidFieldOptions
     */
    public function validator(ValidatorInterface $validator): FluidFieldOptions
    {
        $this->handler->addValidator($validator);
        return $this;
    }

    /**
     * @return FluidHydrator
     */
    public function then(): FluidHydrator
    {
        return $this->hydrator;
    }

    // Wrapped methods from FluidHydrator $hydrator //
    public function getHydrator(): Hydrator { return $this->hydrator->getHydrator(); }
    public function field(string $key): FluidField { return $this->hydrator->field($key); }
    public function handler(HydratingHandlerInterface $handler): FluidHydrator { return $this->hydrator->handler($handler); }
    public function end(): FluidFieldOptions { return $this->hydrator->end(); }
    public function hydrateNewObject(array $data, string $className) { return $this->hydrator->hydrateNewObject($data, $className); }
    public function hydrateObject(array $data, $object) { return $this->hydrator->hydrateObject($data, $object); }
}