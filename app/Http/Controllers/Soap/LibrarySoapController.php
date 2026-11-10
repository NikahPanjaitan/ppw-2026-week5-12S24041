<?php

namespace App\Http\Controllers\Soap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

// Controller SOAP untuk integrasi sistem legacy Bebas Pustaka Yudisium IT Del
class LibrarySoapController extends Controller
{
    // 1. Sajikan dokumen kontrak WSDL (GET /soap/sipus.wsdl)
    public function wsdl(): Response
    {
        $endpointUrl = url('/soap/sipus');
        $wsdlXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<definitions name="LibraryClearanceService"
    targetNamespace="http://sipus.del.ac.id/soap/clearance"
    xmlns:tns="http://sipus.del.ac.id/soap/clearance"
    xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns="http://schemas.xmlsoap.org/wsdl/">

    <!-- 1. Message: format input dan output parameter -->
    <message name="CheckClearanceStatusRequest">
        <part name="nim_peminjam" type="xsd:string"/>
    </message>
    <message name="CheckClearanceStatusResponse">
        <part name="nim_peminjam" type="xsd:string"/>
        <part name="student_name" type="xsd:string"/>
        <part name="active_loans" type="xsd:int"/>
        <part name="is_cleared" type="xsd:boolean"/>
    </message>

    <!-- 2. PortType: operasi yang disediakan -->
    <portType name="LibraryClearancePortType">
        <operation name="CheckClearanceStatus">
            <input message="tns:CheckClearanceStatusRequest"/>
            <output message="tns:CheckClearanceStatusResponse"/>
        </operation>
    </portType>

    <!-- 3. Binding: binding ke SOAP over HTTP -->
    <binding name="LibraryClearanceBinding" type="tns:LibraryClearancePortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="CheckClearanceStatus">
            <soap:operation soapAction="http://sipus.del.ac.id/soap/clearance#CheckClearanceStatus"/>
            <input><soap:body use="literal" namespace="http://sipus.del.ac.id/soap/clearance"/></input>
            <output><soap:body use="literal" namespace="http://sipus.del.ac.id/soap/clearance"/></output>
        </operation>
    </binding>

    <!-- 4. Service: lokasi endpoint fisik layanan -->
    <service name="LibraryClearanceService">
        <port name="LibraryClearancePort" binding="tns:LibraryClearanceBinding">
            <soap:address location="{$endpointUrl}"/>
        </port>
    </service>
</definitions>
XML;

        return response($wsdlXml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    // 2. Tangani request SOAP XML masuk (POST /soap/sipus)
    public function handle(Request $request): Response
    {
        $rawXml = trim($request->getContent());
        if (empty($rawXml)) {
            return $this->soapFault('soap:Client', 'Body XML SOAP tidak boleh kosong.');
        }

        // Parsing XML body dengan aman
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($rawXml);
        if ($xml === false) {
            return $this->soapFault('soap:Client', 'Format XML tidak valid (Malformed XML).');
        }

        // Ambil tag nim_peminjam terlepas dari namespace
        $nimNodes = $xml->xpath('//*[local-name()="CheckClearanceStatusRequest"]/*[local-name()="nim_peminjam"]')
            ?: $xml->xpath('//*[local-name()="nim_peminjam"]');

        if (empty($nimNodes) || trim((string) $nimNodes[0]) === '') {
            return $this->soapFault(
                'soap:Client',
                'Operasi tidak dikenali atau elemen <nim_peminjam> tidak ditemukan pada request.'
            );
        }

        $nim = trim((string) $nimNodes[0]);

        // Cek tanggungan buku yang masih berstatus 'dipinjam' di tabel peminjamans
        $activeLoans = DB::table('peminjamans')
            ->where('nim_peminjam', $nim)
            ->where('status', 'dipinjam')
            ->count();

        // Ambil nama peminjam dari riwayat jika ada
        $riwayat = DB::table('peminjamans')
            ->where('nim_peminjam', $nim)
            ->latest()
            ->first();

        $studentName = $riwayat ? $riwayat->nama_peminjam : "Mahasiswa ({$nim})";
        $isCleared   = ($activeLoans === 0) ? 'true' : 'false';

        $safeNim  = htmlspecialchars($nim, ENT_XML1, 'UTF-8');
        $safeName = htmlspecialchars($studentName, ENT_XML1, 'UTF-8');

        // Bungkus balasan ke amplop SOAP
        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Header>
        <m:ProviderInfo>SIPUS-Del Clearance SOAP Engine v1.0</m:ProviderInfo>
    </soap:Header>
    <soap:Body>
        <m:CheckClearanceStatusResponse>
            <m:nim_peminjam>{$safeNim}</m:nim_peminjam>
            <m:student_name>{$safeName}</m:student_name>
            <m:active_loans>{$activeLoans}</m:active_loans>
            <m:is_cleared>{$isCleared}</m:is_cleared>
        </m:CheckClearanceStatusResponse>
    </soap:Body>
</soap:Envelope>
XML;

        return response($responseXml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    // Helper pembentuk pesan error SOAP Fault
    private function soapFault(string $faultCode, string $faultMessage): Response
    {
        $safeMsg = htmlspecialchars($faultMessage, ENT_XML1, 'UTF-8');
        $faultXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <soap:Fault>
            <faultcode>{$faultCode}</faultcode>
            <faultstring>{$safeMsg}</faultstring>
        </soap:Fault>
    </soap:Body>
</soap:Envelope>
XML;

        return response($faultXml, 500)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }
}
