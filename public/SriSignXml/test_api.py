import requests
import json

# Datos de la factura
invoice_data = {
  "documentInfo": {
    "accessKey": "",
    "businessName": "Carlos Alberto Angulo Pizarro",
    "commercialName": "Carlos Alberto Angulo Pizarro",
    "businessAddress": "Calle: E3J Numero: S56-65 Interseccion: S57 P INOCENCIO JACOME",
    "dayEmission": "28",
    "monthEmission": "08",
    "yearEmission": "2025",
    "codDoc": "01",
    "rucBusiness": "1725874992001",
    "environment": "1",
    "typeEmission": "1",
    "establishment": "001",
    "establishmentAddress": "Calle: E3J Numero: S56-65 Interseccion: S57 P INOCENCIO JACOME",
    "emissionPoint": "001",
    "sequential": "000001494",
    "obligatedAccounting": "NO"
  },
  "customer": {
    "identificationType": "05",
    "customerName": "leonela jaramillo",
    "customerDni": "1728557206",
    "customerAddress": "No especificada"
  },
  "payment": {
    "totalWithoutTaxes": "224.00",
    "totalDiscount": "0.00",
    "gratuity": "0.00",
    "totalAmount": "254.60",
    "currency": "DOLAR",
    "paymentMethodCode": "16",
    "totalPayment": "254.60",
    "paymentTerm": "0",
    "timeUnit": "dias"
  },
  "details": [
    {
      "productCode": "EXAMENVISUAL",
      "productName": "Examen Visual",
      "description": "Examen Visual",
      "quantity": 1.00,
      "price": "10.00",
      "discount": "0.00",
      "subTotal": "10.00",
      "taxTypeCode": "2",
      "percentageCode": "0",
      "rate": "0.00",
      "taxableBaseTax": "10.00",
      "taxValue": "0.00"
    },
    {
      "productCode": "ARMAZONACCESORIOS",
      "productName": "Armazon/Accesorios", 
      "description": "Armazon/Accesorios",
      "quantity": 1.00,
      "price": "64.00",
      "discount": "0.00",
      "subTotal": "64.00",
      "taxTypeCode": "2",
      "percentageCode": "4",
      "rate": "15.00",
      "taxableBaseTax": "64.00",
      "taxValue": "9.60"
    },
    {
      "productCode": "CRISTALERIA",
      "productName": "Cristaleria",
      "description": "Cristaleria", 
      "quantity": 1.00,
      "price": "140.00",
      "discount": "0.00",
      "subTotal": "140.00",
      "taxTypeCode": "2",
      "percentageCode": "4",
      "rate": "15.00",
      "taxableBaseTax": "140.00",
      "taxValue": "21.00"
    },
    {
      "productCode": "COMPRARAPIDA",
      "productName": "Servicio de compra rapida",
      "description": "Servicio de compra rapida",
      "quantity": 1.00,
      "price": "10.00",
      "discount": "0.00",
      "subTotal": "10.00",
      "taxTypeCode": "2",
      "percentageCode": "0",
      "rate": "0.00",
      "taxableBaseTax": "10.00",
      "taxValue": "0.00"
    }
  ],
  "totalsWithTax": [
    {
      "taxCode": "2",
      "percentageCode": "4",
      "taxableBase": "204.00",
      "taxValue": "30.60"
    },
    {
      "taxCode": "2", 
      "percentageCode": "0",
      "taxableBase": "20.00",
      "taxValue": "0.00"
    }
  ],
  "additionalInfo": [
    {
      "name": "Observaciones",
      "value": "SISTEMA DE FACTURACION ELECTRONICA"
    }
  ]
}

try:
    print("Enviando petición a la API...")
    response = requests.post(
        "http://127.0.0.1:8000/invoice/sign",
        json=invoice_data,
        headers={"Content-Type": "application/json"}
    )
    
    print(f"Status Code: {response.status_code}")
    print(f"Response: {response.text}")
    
    if response.status_code == 200:
        result = response.json()
        print("\n✅ ¡Factura procesada exitosamente!")
        print(f"Clave de acceso: {result['result']['accessKey']}")
        print(f"Recibida: {result['result']['isReceived']}")
        print(f"Autorizada: {result['result']['isAuthorized']}")
        if result['result']['xmlFileSigned']:
            print("✅ XML firmado generado correctamente")
        else:
            print("❌ No se generó el XML firmado")
    else:
        print(f"❌ Error: {response.status_code}")
        print(response.text)
        
except Exception as e:
    print(f"❌ Error al conectar con la API: {e}")
