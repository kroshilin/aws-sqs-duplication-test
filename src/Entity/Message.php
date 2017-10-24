<?php
namespace app\Entity;

/**
 * @Entity @Table(name="messages")
 **/
class Message
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $data;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $awsId;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $receiveCount;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $receipt;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getAwsId(): ?string
    {
        return $this->awsId;
    }

    /**
     * @param string $awsId
     */
    public function setAwsId(string $awsId)
    {
        $this->awsId = $awsId;
    }

    /**
     * @return int
     */
    public function getReceiveCount(): ?int
    {
        return $this->receiveCount;
    }

    /**
     * @param int $receiveCount
     */
    public function setReceiveCount(int $receiveCount)
    {
        $this->receiveCount = $receiveCount;
    }

    /**
     * @return string
     */
    public function getReceipt(): ?string
    {
        return $this->receipt;
    }

    /**
     * @param string $receipt
     */
    public function setReceipt(string $receipt)
    {
        $this->receipt = $receipt;
    }



    public function __toString()
    {
        return json_encode(['data' => $this->data]);
    }
}