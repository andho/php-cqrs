<?php

namespace Domain\Model;

class EventSourced {
	//abstract protected function applyEvent($event);
	protected function unhandled($event) {
		throw new \Exception("This event is not handled");
	}
}

class AggregateRoot extends EventSourced {
	
	public $uncommitedEvents = array();
	
	protected function markCommited() {
		$this->uncommitedEvents = array();
	}
	
	protected function record($event) {
		$this->applyEvent($event);
		$this->uncommitedEvents[] = $event;
	}
	
}

class AggregateFactory extends EventSourced {
	
	public function loadFromHistory($history) {
		$invoice = $this->applyEvent(array_shift($history));
		foreach ($history as $event) {
			$invoice->applyEvent($event);
		}
		return $invoice->markCommitted();
	}
	
}

class InvoiceFactory extends AggregateFactory {
	
	public function create($id, $recipient) {
		return $this->applyEvent(new InvoiceCreated($id, $recipient));
	}
	
	public function applyEvent($event) {
		$event_type = get_class($event);
		switch($event_type) {
			case 'Domain\Model\InvoiceCreated':
				$invoice = new InvoiceDraft($event->id, array($event), $event->recipient);
				break;
		}
		
		return $invoice;
	}
	
}

abstract class InvoiceEvent {
	public $id;
	public function __construct($id) {
		$this->id = $id;
	}
}

class InvoiceCreated extends InvoiceEvent {
	public $recipient;
	public function __construct($id, $recipient) {
		parent::__construct($id);
		$this->recipient = $recipient;
	}
}

class InvoiceRecipientChanged extends InvoiceEvent {
	public $recipient;
	public function __construct($id, $recipient) {
		parent::__construct($id);
		$this->recipient = $recipient;
	}
}

class InvoiceItemAdded extends InvoiceEvent {
	public $item;
	public function __construct($id, $item) {
		parent::__construct($id);
		$this->item = $item;
	}
}

class InvoiceItemRemoved extends InvoiceEvent {
	public $item;
	public function __construct($id, $item) {
		parent::__construct($id);
		$this->item = $item;
	}
}

class InvoiceSent extends InvoiceEvent {
	public $sentDate;
	public $dueDate;
	public function __construct($id, $sentDate, $dueDate) {
		parent::__construct($id);
		$this->sentDate = $sentDate;
		$this->dueDate = $dueDate;
	}
}

class InvoiceReminderEvent extends InvoiceEvent {
	public $reminderDate;
	public function __construct($id, $reminderDate) {
		parent::__construct($id);
		$this->reminderDate = $reminderDate;
	}
}

class InvoicePaymentReceived extends InvoiceEvent {
	public $paymentDate;
	public function __construct($id, $paymentDate) {
		parent::__construct($id);
		$this->paymentDate = $paymentDate;
	}
}