<?php

namespace Tests\Integration;

use Aternos\Rados\Cluster\ClientVersionRequirement;
use Aternos\Rados\Cluster\ClusterConfig;
use Aternos\Rados\Cluster\ClusterStat;
use Aternos\Rados\Cluster\CommandResult;
use Tests\RadosTestCase;

class ClusterTest extends RadosTestCase
{
    public function testGetClusterStat(): void
    {
        $stat = $this->getCluster()->clusterStat();

        $this->assertInstanceOf(ClusterStat::class, $stat);
        $this->assertGreaterThanOrEqual(0, $stat->getKB());
        $this->assertGreaterThanOrEqual(0, $stat->getKBUsed());
        $this->assertGreaterThanOrEqual(0, $stat->getKbAvail());
        $this->assertGreaterThanOrEqual(0, $stat->getNumObjects());
    }

    public function testGetFsid(): void
    {
        $fsid = $this->getCluster()->getFsid();
        $this->assertIsString($fsid);
        $this->assertEquals(36, strlen($fsid));
    }

    public function testListPools(): void
    {
        $pools = $this->getCluster()->listPools();
        $this->assertIsArray($pools);
        $this->assertGreaterThanOrEqual(0, count($pools));
        foreach ($pools as $pool) {
            $this->assertIsString($pool);
        }
    }

    public function testGetConfigHandle(): void
    {
        $configHandle = $this->getCluster()->getConfigHandle();
        $this->assertInstanceOf(ClusterConfig::class, $configHandle);
    }

    public function testGetInstanceId(): void
    {
        $instanceId = $this->getCluster()->getInstanceId();
        $this->assertIsInt($instanceId);
    }

    public function testGetMinCompatibleOsd(): void
    {
        $minVersion = $this->getCluster()->getMinCompatibleOsd();
        $this->assertIsInt($minVersion);
    }

    public function testGetMinCompatibleClient(): void
    {
        $minVersion = $this->getCluster()->getMinCompatibleClient();
        $this->assertInstanceOf(ClientVersionRequirement::class, $minVersion);
        $this->assertIsInt($minVersion->getMinCompatibleClient());
        $this->assertIsInt($minVersion->getRequiredMinCompatibleClient());
    }

    public function testGetAddress(): void
    {
        $address = $this->getCluster()->getAddress();
        $this->assertIsString($address);
    }

    public function testIsConnected(): void
    {
        $this->assertTrue($this->getCluster()->isConnected());
    }

    public function testSendMonitorCommand(): void
    {
        $response = $this->getCluster()->sendMonitorCommand(['{ "prefix": "osd tree" }'], "");
        $this->assertInstanceOf(CommandResult::class, $response);
    }
}
