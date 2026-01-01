<!DOCTYPE html>
<html>

<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            text-align: left;
        }

        th {
            background-color: #eee;
        }

        .total {
            font-weight: bold;
        }

        .address {
            font-size: 12px;
            line-height: 1.2;
        }
    </style>
</head>

<body>
    <table style="width: 100%; border:none">
        <tr>
            <td style="border:none"><img class="img" src="{{ public_path('header.svg') }}" height="4%" /></td>
            <td width="80%" style="border:none;">
                <div style="font-weight: bold; font-size:1rem;text-wrap:none">
                    PT MUMTAZ CERIA EDUKASI
                </div>
                <div class="address">
                    Jl. Saditan Baru, Saditan, Brebes, Kec. Brebes, Kabupaten Brebes, Jawa Tengah 52212<br>
                    (Gg. Flamboyan RT 06/RW 05, Kel. Brebes)
                </div>
                <div class="contact" style="font-size: 11px">
                    Website: <a href="http://www.murikaceria.co.id" target="_blank">www.Murikaceria.co.id</a> |
                    Email: <a href="mailto:Murika.mumtaz@yahoo.com">Murika.mumtaz@yahoo.com</a> |
                    Facebook: Murika Ceria
                </div>
            </td>
        </tr>
    </table>
    <div style="width: 100%; height:1px; background-color:black;margin-bottom:0.1rem;margin-top:1rem"></div>
    <div style="width: 100%; height:2.5px; background-color:black;margin-bottom:1rem"></div>
    <table style="width: 100%; border:none; font-size:12px;margin-bottom:2rem">
        <tr style="background-color: #C51E3A; color:#F0F8FF">
            <td colspan="2" style="border: none;padding:0px;font-weight:bold">&nbsp;Data Registrasi</td>
        </tr>
        <tr>
            <td style="border: none" width="30%">Nomor Induk</td>
            <td style="border:none">: {{ $items->reg->induk }}</td>
        </tr>
        <tr>
            <td style="border: none" width="30%">Nama</td>
            <td style="border:none">: {{ $items->reg->murid->name }}</td>
        </tr>
        <tr>
            <td style="border: none" width="30%">Alamat</td>
            <td style="border:none">: {{ $items->reg->murid->alamat }}</td>
        </tr>
        <tr>
            <td style="border: none">Kelas</td>
            <td style="border:none">: {{ $items->reg->product->class->name }}</td>
        </tr>
        <tr>
            <td style="border: none">Program Belajar</td>
            <td style="border:none">: {{ $items->reg->product->program->name }}</td>
        </tr>
        <tr>
            <td style="border: none">Jenis Pembayaran</td>
            <td style="border:none">: {{ $items->reg->kontrak->name }} ({{ $items->reg->kontrak->month }} Bulan)</td>
        </tr>
        <tr>
            <td style="border: none">Unit</td>
            <td style="border:none">: {{ $items->reg->units->name }}</td>
        </tr>
    </table>

    <table style="width: 100%; border:none; font-size:12px;margin-bottom:2rem">
        <tr style="background-color: #C51E3A; color:#F0F8FF">
            <td colspan="2" style="border: none;padding:0px;font-weight:bold">&nbsp;Detail Pembayaran</td>
        </tr>
        <tr>
            <td style="border: none" width="30%">Bulan</td>
            <td style="border:none">: {{ $items->bulan }}</td>
        </tr>
        <tr>
            <td style="border: none" width="30%">Nominal</td>
            @php
                $kit = $items->kit ? $items->kit->price->harga : 0;
            @endphp
            <td style="border:none">: {{ number_format($items->reg->product->harga + $kit, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="border: none" width="30%">Jatuh Tempo</td>
            <td style="border:none">: {{ $items->tempo }}</td>
        </tr>
    </table>

    <div style="font-size: 12px">Ketentuan pembayaran :</div>
    <ol style="font-size: 12px;">
        <li>Pembayaran hanya dapat dilakukan <strong>sebelum tanggal kedaluwarsa</strong>...</li>
        <li>Pembayaran dapat dilakukan melalui berbagai <strong>payment channel</strong> seperti...</li>
        <!-- dan seterusnya -->
    </ol>
</body>

</html>
