<?php

namespace App\Tests\Unit\Service\RedisStorage;

use App\Service\Raport\RaportDto;
use App\Service\Raport\RaportFactory;
use App\Service\RedisStorage\ReportStorage;
use PHPUnit\Framework\TestCase;
use Redis;

class ReportStorageTest extends TestCase
{
    private Redis $redisMock;
    private ReportStorage $reportStorage;

    protected function setUp(): void
    {
        $this->redisMock = $this->createMock(Redis::class);

        $this->reportStorage = new ReportStorage();
        $reflection = new \ReflectionClass($this->reportStorage);
        $property = $reflection->getProperty('redis');
        $property->setAccessible(true);
        $property->setValue($this->reportStorage, $this->redisMock);
    }

    public function testSaveReport(): void
    {
        $raportDto = new RaportDto(
            id: '12345',
            filePath: '/uploads/data.csv',
            status: 'completed',
            created_at: '2025-02-17 12:00:00',
            startTime: '2025-02-17 12:01:00'
        );

        $raportDto->setEndTime('2025-02-17 12:05:00');
        $raportDto->setDuration('240s');
        $raportDto->setProcessedRows('1000');
        $raportDto->setErrors(['Invalid row 5', 'Missing column in row 10']);

        $this->redisMock
            ->expects($this->once())
            ->method('set')
            ->with('report:12345', json_encode($raportDto->toArray()));

        $this->reportStorage->save($raportDto);
    }

    public function testGetReportReturnsDto(): void
    {
        $reportId = '12345';
        $reportArray = [
            'id' => $reportId,
            'filePath' => '/uploads/data.csv',
            'status' => 'completed',
            'created_at' => '2025-02-17 12:00:00',
            'startTime' => '2025-02-17 12:01:00',
            'endTime' => '2025-02-17 12:05:00',
            'duration' => '240s',
            'processedRows' => '1000',
            'errors' => ['Invalid row 5', 'Missing column in row 10']
        ];

        $this->redisMock
            ->expects($this->once())
            ->method('get')
            ->with("report:$reportId")
            ->willReturn(json_encode($reportArray));

        $result = $this->reportStorage->get($reportId);

        $this->assertInstanceOf(RaportDto::class, $result);
        $this->assertEquals($reportId, $result->getId());
        $this->assertEquals('completed', $result->getStatus());
        $this->assertEquals('240s', $result->getDuration());
    }

    public function testGetReportThrowsExceptionWhenNotFound(): void
    {
        $reportId = 'non-existent';

        $this->redisMock
            ->expects($this->once())
            ->method('get')
            ->with("report:$reportId")
            ->willReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(sprintf('Report with id %s not found', $reportId));

        $this->reportStorage->get($reportId);
    }
}
