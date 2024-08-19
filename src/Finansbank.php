<?php

namespace Phpdev;

class Finansbank
{
    private $username;
    private $password;
    private $wsdlUrl;

    public function __construct($username, $password, $wsdlUrl)
    {
        $this->username = $username;
        $this->password = $password;
        $this->wsdlUrl = $wsdlUrl;
    }

    private function formatDate($date)
    {
        // Tarih formatını gerekli formata çevirir.
        return date('Y-m-d\TH:i:s', strtotime($date));
    }

    public function getTransactionInfo($startDate, $endDate, $accountNo = '', $iban = '')
    {
        try {
            $formattedStartDate = $this->formatDate($startDate);
            $formattedEndDate = $this->formatDate($endDate);

            $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ss2="http://ss2ekstre.genericekstrenew.driver.maestro.ibtech.com">
   <soapenv:Header/>
   <soapenv:Body>
      <ss2:getTransactionInfo>
         <transactionInfo>
            <password>{$this->password}</password>
            <transactionInfoInputType>
               <accountNo>{$accountNo}</accountNo>
               <endDate>{$formattedEndDate}</endDate>
               <iban>{$iban}</iban>
               <startDate>{$formattedStartDate}</startDate>
            </transactionInfoInputType>
            <userName>{$this->username}</userName>
         </transactionInfo>
      </ss2:getTransactionInfo>
   </soapenv:Body>
</soapenv:Envelope>
XML;

            $headers = array(
                'SOAPAction: urn:getTransactionInfo',
                'Content-Type: text/xml;charset=UTF-8',
            );

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->wsdlUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $xml,
                CURLOPT_HTTPHEADER => $headers,
            ));
        
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
        
            if ($err) {
                $res['statu'] = false;
                $res['response'] = 'cURL error: ' . $err;
            } else {
                $res['statu'] = true;
                $res['response'] = $response;
            }
            return json_encode($res);
        
        } catch (Throwable $e) {
            $res['statu'] = false;
            $res['response'] = 'Bağlantı problemi oluştu.';
            return json_encode($res);
        }
    }
}