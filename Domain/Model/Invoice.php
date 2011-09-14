<?php

namespace Domain\Model;

require_once 'Helpers.php';

abstract class Invoice extends AggregateRoot {
	protected $id;
	public function __construct($id, $uncommitted_events) {
		$this->uncommitedEvents = $uncommitted_events;
		$this->id = $id;
	}
}

class InvoiceDraft extends Invoice {
	
	private $recipient;
	private $nextItemId;
	private $items = array();
	private $totalAmount;
	
	public function __construct($id, $uncommitted_events, $recipient) {
		parent::__construct($id, $uncommitted_events);
		$this->recipient = $recipient;
	}
	
	public function removeItem($index) {
		if ($this->isSent()) {
			throw new \Exception("Cannot remove items from sent invoices");
		}
		
		$item = $this->_items[$index];
		unset($this->_items[$index]);
		
		$this->_totalAmount -= $item;
	}
	
	public function send() {
		$sentDate = date('Y-m-d');
		$dueDate = date('Y-m-d', strtotime($sentDate . ' +14 days'));
		return $this->applySent(new InvoiceSent($this->id, $sentDate, $dueDate));
	}
	
	public function applySent($event) {
		$this->uncommitedEvents[] = $event;
		$invoice = new SentInvoice($this->id, $this->uncommitedEvents, $event->sentDate, $event->dueDate);
		return $invoice;
	}
	
	public function applyEvent($event) {
		
	}
	
}

class SentInvoice extends Invoice {
	
	private $paid;
	private $sentDate;
	private $dueDate;
	
	public function __construct($id, $uncommitted_events, $sentDate, $dueDate) {
		parent::__construct($id, $uncommitted_events);
		$this->sendDate = $sentDate;
		$this->dueDate = $dueDate;
	}
	
	public function ApplyEvent($event) {
		
	}
	
}

$fac = new InvoiceFactory();

$invoice = $fac->create(1, 'me')
	->send();

var_dump($invoice->uncommitedEvents);
