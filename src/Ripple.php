<?php

namespace MaxMadKnight\RippleAPI;

use MaxMadKnight\RippleAPI\Objects\AccountObject;
use MaxMadKnight\RippleAPI\Objects\PaymentObject;
use MaxMadKnight\RippleAPI\Objects\SignObject;
use MaxMadKnight\RippleAPI\Objects\TransactionObject;
use MaxMadKnight\RippleAPI\Transaction\TransactionBuilder;

class Ripple
{
    /**
     * Ripple wallet address
     *
     * @var string
     */
    protected $address;

    /**
     * Private key of ripple wallet
     *
     * @var string
     */
    protected $secret;

    /**
     * Ripple client service
     *
     * @var RippleClient
     */
    protected $client;

    /**
     * Hash of signed transaction
     *
     * @var string
     */
    protected $tx_blob;

    /**
     * Ripple
     *
     * @param       $address
     * @param null  $secret
     * @param array $nodes
     */
    public function __construct($address, $secret = null, $nodes = [])
    {
        $this->address = $address;
        $this->secret = $secret;

        $this->client = new RippleClient($nodes);
    }

    /**
     * Ping
     *
     * @return array
     */
    public function getPing(): array
    {
        return $this->call('ping', '/');
    }

    /**
     * Base function
     *
     * @param       $method
     * @param       $path
     * @param array $params
     *
     * @return array
     */
    protected function call($method, $path, $params = [])
    {
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            return $this->client->sendRequest(
                $method,
                trim($path),
                $params,
                true
            );
        } else {
            return $this->client->sendRequest(
                $method,
                trim($path),
                $params,
                false
            );
        }
    }

    /**
     * Get Server info
     *
     * @return array
     */
    public function getServerInfo(): array
    {
        return $this->call('server_info', '/');
    }

    /**
     * Generation of random digest
     *
     * @return array
     */
    public function getRandom(): array
    {
        return $this->call('random', '/');
    }

    /**
     * Get list of active accounts
     *
     * @param array $params
     *
     * @return array
     */
    public function getAccounts($params = [])
    {
        return $this->call('GET', '/accounts', $params);
    }

    /**
     * Get info about account
     *
     * @param null $address
     *
     * @return AccountObject
     */
    public function getAccount($address = null): AccountObject
    {
        $address = ($address == null ? $this->address : $address);
        $response = $this->call('GET', sprintf('/accounts/%s', $address));

        return new AccountObject($response['account_data']);
    }

    /**
     * Get Balance
     *
     * @param null  $address
     * @param array $params
     *
     * @return array
     */
    public function getAccountBalances($address = null, $params = []): array
    {
        $address = ($address == null ? $this->address : $address);
        $response = $this->call('GET', sprintf('/accounts/%s/balances', $address), $params);

        return $response;
    }

    /**
     * List of transactions
     *
     * @param null  $address
     * @param array $params
     *
     * @return PaymentObject | array
     */
    public function getAccountPayments($address = null, $params = [])
    {
        $address = ($address == null ? $this->address : $address);
        $response = $this->call('GET', sprintf('/accounts/%s/payments', $address), $params);

        if ($response['count'] == 1) {
            return new PaymentObject($response['payments'][0]);
        } else {
            return $response['payments'];
        }
    }

    /**
     * Get orders
     *
     * @param       $address
     * @param array $params
     *
     * @return array
     */
    public function getAccountOrder($address = null, $params = [])
    {
        $address = ($address == null ? $this->address : $address);
        return $this->call('GET', sprintf('/account/%s/orders', $address), $params);
    }

    /**
     * Get history of transactions
     *
     * @param       $address
     * @param array $params
     *
     * @return TransactionObject
     */
    public function getAccountTransactionHistory($address = null, $params = [])
    {
        $address = ($address == null ? $this->address : $address);
        $response = $this->call('GET', sprintf('/accounts/%s/transactions', $address), $params);

        return new TransactionObject($response['transactions']);
    }

    /**
     * Get transaction by wallet address and sequence
     *
     * @param null  $address
     * @param null  $sequence
     * @param array $params
     *
     * @return array
     */
    public function getTransactionAccountAndSequence($address = null, $sequence = null, $params = [])
    {
        $address = ($address == null ? $this->address : $address);
        return $this->call('GET', sprintf('/accounts/%s/transactions/%s', $address, $sequence), $params);
    }

    /**
     * Get transaction statistic by wallet address
     *
     * @param null  $address
     * @param array $params
     *
     * @return array
     */
    public function getAccountTransactionStats($address = null, $params = [])
    {
        $address = ($address == null ? $this->address : $address);
        return $this->call('GET', sprintf('/accounts/%s/stats/transactions', $address), $params);
    }

    /**
     * Get account statistic by wallet address
     *
     * @param null  $address
     * @param array $params
     *
     * @return array
     */
    public function getAccountValueStat($address = null, $params = [])
    {
        $address = ($address == null ? $this->address : $address);
        return $this->call('GET', sprintf('/accounts/%s/stats/value', $address), $params);
    }

    /**
     * Get transaction info
     *
     * @param null  $hash
     * @param array $params
     *
     * @return TransactionObject | array
     */
    public function getTransaction($hash = null, $params = [])
    {
        $response = $this->call('GET', '/transactions/' . $hash, $params);

        if (isset($response['count']) and $response['count'] > 1) {
            return $response['transactions'];
        }
        return new TransactionObject($response['transaction']);
    }

    /**
     * Get last rippled versions
     *
     * @return array
     */
    public function getRippledVersion()
    {
        return $this->call('GET', '/network/rippled_versions');
    }

    /**
     * Get geteways
     *
     * @return array
     */
    public function getGateways()
    {
        return $this->call('GET', '/gateways');
    }

    /**
     * Get gateway info
     *
     * @param $gateway
     *
     * @return array
     */
    public function getGateway($gateway)
    {
        return $this->call('GET', '/gateways/' . $gateway);
    }

    /**
     * API Health Check
     *
     * @param array $params
     *
     * @return array
     */
    public function getHealthCheck($params = [])
    {
        return $this->call('GET', '/health/api', $params);
    }

    /**
     * API Health Check Importer
     *
     * @param array $params
     *
     * @return array
     */
    public function getHealthImporter($params = [])
    {
        return $this->call('GET', '/health/importer', $params);
    }

    /**
     * API Health Check - ETL nodes
     *
     * @param array $params
     *
     * @return array
     */
    public function getHealthNodesEtl($params = [])
    {
        return $this->call('GET', '/health/nodes_etl', $params);
    }

    /**
     * API Health Check - ETL
     *
     * @param array $params
     *
     * @return array
     */
    public function getHealthValidationsEtl($params = [])
    {
        return $this->call('GET', '/health/validations_etl', $params);
    }

    /**
     * Get fee
     *
     * @return array
     */
    public function getFee()
    {
        return $this->call('fee', '/');
    }

    /**
     * Verify transaction
     *
     * @param $tx
     *
     * @return array
     */
    public function verifyTransaction($tx)
    {
        return $this->call('tx', '/', [
            'transaction' => $tx
        ]);
    }

    /**
     * Get list of transactions
     *
     * @param array $params
     *
     * @return array
     */
    public function getTransactions($params = [])
    {
        return $this->call('GET', '/transactions', $params);
    }

    /**
     * Get Account info
     *
     * @param array $params
     *
     * @return array
     */
    public function getStats($params = [])
    {
        return $this->call('GET', '/stats', $params);
    }

    /**
     * Create new transaction
     *
     * @param \Closure $closure
     *
     * @return Ripple|null
     */
    public function buildTransaction(\Closure $closure): ?self
    {
        $payment = new TransactionBuilder();
        $payment->setSecret($this->secret);
        $payment->setAccount($this->address);

        if ($closure instanceof \Closure) {
            $response = $this->call('sign', '/', $closure->call($payment, $payment));
            if ($response['result']['status'] == 'success') {
                $this->tx_blob = (new SignObject($response['result']))->getTxBlob();
            }

            return $this;
        }

        return null;
    }

    /**
     * Send signed transaction to Ripple
     *
     * @return array
     * @throws \Exception
     */
    public function submit()
    {
        $result = $this->call('submit', '/', [
            'tx_blob' => $this->tx_blob
        ]);

        if (empty($result)) {
            throw new \Exception('Sign is invalid');
        } else {
            return $result;
        }
    }

    /**
     * Send funds by third-party server
     *
     * @param $options
     *
     * @return array
     * @throws \Exception
     */
    public function sendAndSubmitForServer($options)
    {
        $result = $this->client->sendRequestWss('POST', '/send-xrp', $options);

        if (empty($result)) {
            throw new \Exception('Transaction did not send');
        } else {
            return $result;
        }
    }

    /**
     * Pull current rate
     *
     * @param array         $options
     * @param string        $counter
     * @param string        $currency
     * @param null|string   $address
     *
     * @return array
     */
    public function getExchangeRates($options, $counter = 'XRP', $currency = 'USD', $address = null)
    {
        $address = ($address == null ? $this->address : $address);
        return $this->call('GET', sprintf('/exchange_rates/%s+%s/%s', $currency, $address, $counter), $options);
    }
}
