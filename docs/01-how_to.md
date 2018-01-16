## How to use it?

Let's take as an example a scenario were you have Orders and Customers. First thing we need to do is to create an order,
this will be our *AggregateRoot*:

```php
<?php

namespace HelloFresh\Order\Domain;

use Collections\VectorInterface;
use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\EventSourcing\AggregateRootTrait;
use HelloFresh\Order\Domain\Event\OrderApproved;
use HelloFresh\Order\Domain\Event\OrderCancelled;
use HelloFresh\Order\Domain\Event\OrderCreated;

class Order implements AggregateRootInterface
{
    use AggregateRootTrait;

    /**
     * @var OrderId
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * This makes sure that we use named constructors
     */
    private function __construct()
    {

    }

    /**
     *
     * @param OrderId $id
     * @param Customer $customer
     * @return Order
     */
    public static function placeOrder(OrderId $id, Customer $customer) : Order
    {
        $order = new static();
        $order->recordThat(new OrderCreated($id, $customer));

        return $order;
    }

    /**
     * @return string
     */
    public function getAggregateRootId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus() : string
    {
        return $this->status;
    }

    /**
     * @return Customer
     */
    public function getCustomer() : Customer
    {
        return $this->customer;
    }

    public function approve()
    {
        $this->recordThat(new OrderApproved($this->id));
    }

    public function cancel()
    {
        $this->recordThat(new OrderCancelled($this->id));
    }

    private function whenOrderCreated(OrderCreated $event)
    {
        $this->id = $event->getId();
        $this->customer = $event->getCustomer();
    }

    private function whenOrderApproved(OrderApproved $event)
    {
        $this->status = 'Approved';
    }

    private function whenOrderOrderCancelled(OrderCancelled $event)
    {
        $this->status = 'Cancelled';
    }
}
```

And an Order id is important as well since this is an aggregate root:

```php
use HelloFresh\Engine\Domain\AggregateId;

class OrderId extends AggregateId
{

}
```

Now we need to take care of the events that we want to record (that's the goal of event sourcing), so let's create themm:

```php
use HelloFresh\Engine\Domain\DomainEventInterface;

abstract class AbstractOrderEvent implements DomainEventInterface
{
    /**
     * @var OrderId
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $occurredOn;

    /**
     * OrderCreated constructor.
     * @param OrderId $id
     */
    public function __construct(OrderId $id)
    {
        $this->id = $id;
        $this->occurredOn = new \DateTime();
    }

    /**
     * @return OrderId
     */
    public function getId() : OrderId
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function occurredOn() : \DateTime
    {
        return $this->occurredOn;
    }
}

```

```php
final class CustomerAssigned extends AbstractOrderEvent
{
    /**
     * @var Customer
     */
    private $customer;

    public function __construct(OrderId $id, Customer $customer)
    {
        parent::__construct($id);
        $this->customer = $customer;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
```

```php
final class OrderCreated extends AbstractOrderEvent
{
    /**
     * @var Customer
     */
    private $customer;

    public function __construct(OrderId $id, Customer $customer)
    {
        parent::__construct($id);
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
```

```php
final class OrderApproved extends AbstractOrderEvent
{

}
```

```php
final class OrderCancelled extends AbstractOrderEvent
{
   
}
```

Finally after having all events we can start recording them with the `recordThat` method, as you saw on the `Order`
class.
Let's check how that works:

```php
$customer = new Customer(CustomerId::generate()); //of course this won't be generated every time
$order = Order::place(OrderId::generate(), $customer);

var_dump($order->getUncommitedEvents()); //you'll get an EventStream with the events that happened 
```

Now all of this needs to make sense for our application... We need to store these events somewhere. For this task 
we have the `EventSourcingRepository` class, that takes care of this for us. Let's create a repository for our orders.


```php
interface WriteOrderRepositoryInterface
{
    public function nextIdentity() : OrderId;

    public function add(Order $order) : WriteOrderRepositoryInterface;
}

class WriteOrderRepository implements WriteOrderRepositoryInterface
{
    /**
     * @var EventSourcingRepositoryInterface
     */
    private $eventStoreRepo;

    /**
     * RedisWriteOrderRepository constructor.
     * @param EventSourcingRepositoryInterface $eventStoreRepo
     */
    public function __construct(EventSourcingRepositoryInterface $eventStoreRepo)
    {
        $this->eventStoreRepo = $eventStoreRepo;
    }

    public function nextIdentity() : OrderId
    {
        return OrderId::generate();
    }

    public function add(Order $order) : WriteOrderRepositoryInterface
    {
        $this->eventStoreRepo->save($order);

        return $this;
    }
}
```

There we go, now let's see how to use it.

```php
        // We need to configure an event bus (this is how you can hook projections)
        $eventBus = new SimpleEventBus();
        
        //For this example let's use the InMemoryAdapter (You can use Redis, Mongo and DBAL)
        $eventStore = new EventStore(new InMemoryAdapter());
        
        //Creates the event sourcing repo
        $aggregateRepo = new EventSourcingRepository($eventStore, $eventBus);
        
        //Creates the order repository and saves it
        $writeOrderRepo = new WriteOrderRepository($aggregateRepo);
        $writeOrderRepo->add($order);
```

That's a bit of work to get it done, even if you use IoC, so let's use a factory to build this:

```php
        //Creates the event sourcing repo
        $factory = new AggregateRepositoryFactory([
            'event_store' => [
                'adapter' => 'in_memory'
            ]
        ]);
        
        $aggregateRepo = $factory->build();
        
        //Creates the order repository and saves it
        $writeOrderRepo = new WriteOrderRepository($aggregateRepo);
        $writeOrderRepo->add($order);
```

Now it looks better, so you can use it both ways, to have flexibility (Specially with IoC) use the first one, to 
build something quickly use the second one.
Ok we have our repository working, now let's bind this together with the power of *Command Pattern*. Let's build a
command to create the order:

```php
class CreateOrderCommand
{
    /**
     * @var string
     */
    private $customerId;

    /**
     * CreateOrderCommand constructor.
     * @param $customerId
     */
    public function __construct($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return mixed
     */
    public function getCustomerId() : string
    {
        return $this->customerId;
    }
}
```

And the handler will look something like this:

```php
class CreateOrderHandler
{
    /**
     * @var WriteOrderRepositoryInterface
     */
    private $repo;

    /**
     * CreateOrderHandler constructor.
     * @param WriteOrderRepositoryInterface $repo
     */
    public function __construct(WriteOrderRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function handle(CreateOrderCommand $command)
    {
        $customerId = CustomerId::fromString($command->getCustomerId());
        $customer = new Customer($customerId);
        $order = Order::placeOrder($this->repo->nextIdentity(), $customer);

        $this->repo->add($order);
    }
}
```

And to use it:

```php
$commandBus = new SimpleCommandBus();
$commandBus->subscribe(CreateOrderCommand::class, new CreateOrderHandler($writeRepo));

$command = new CreateOrderCommand(CustomerId::generate()); // Again the customer id will probably come from somewhere 
else
$commandBus->execute($command);
```

And that's it, with this small tutorial you have all the power of event sourcing in you application.
