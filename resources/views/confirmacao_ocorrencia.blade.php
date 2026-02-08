@php
    /**
     * STATUS:
     * 1 - Sucesso
     * 2 - Link inválido / expirado
     * 3 - Recebimento cancelado
     */
    $map = [
        1 => [
            'titulo' => 'Recebimento Confirmado!',
            'mensagem' =>
                'A entrega do seu objeto foi registada com sucesso no sistema de Achados e Perdidos. A ocorrência foi oficialmente encerrada.',
            'bgIcon' => '#dcfce7',
            'iconColor' => '#16a34a',
            'iconPath' => 'M5 13l4 4L19 7',
            'cta' => true,
        ],
        2 => [
            'titulo' => 'Link Inválido ou Expirado',
            'mensagem' =>
                'Este link de confirmação não é mais válido ou já foi utilizado. Caso tenha dúvidas, entre em contacto com o suporte.',
            'bgIcon' => '#fee2e2',
            'iconColor' => '#dc2626',
            'iconPath' => 'M6 18L18 6M6 6l12 12',
            'cta' => false,
        ],
        3 => [
            'titulo' => 'Recebimento Cancelado',
            'mensagem' =>
                'O processo de confirmação foi cancelado e a ocorrência não foi encerrada. Se isto foi um erro, pode iniciar um novo pedido.',
            'bgIcon' => '#fef3c7',
            'iconColor' => '#d97706',
            'iconPath' => 'M12 8v4m0 4h.01',
            'cta' => true,
        ],
    ];

    $statusData = $map[$status ?? 2];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Achados e Perdidos') }}</title>
</head>

<body
    style="margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
           background-color:#f3f4f6; display:flex; justify-content:center;
           align-items:center; min-height:100vh;">

    <div
        style="background-color:#ffffff; padding:40px; border-radius:16px;
               box-shadow:0 10px 25px rgba(0,0,0,0.1);
               text-align:center; max-width:420px; width:90%;">

        <!-- ÍCONE -->
        <div
            style="background-color:{{ $statusData['bgIcon'] }};
                   width:80px; height:80px; border-radius:50%;
                   display:flex; justify-content:center; align-items:center;
                   margin:0 auto 20px;">
            <svg style="width:40px; height:40px; color:{{ $statusData['iconColor'] }};" fill="none"
                stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="{{ $statusData['iconPath'] }}">
                </path>
            </svg>
        </div>

        <!-- TÍTULO -->
        <h1 style="color:#111827; margin:0 0 10px; font-size:24px;">
            {{ $statusData['titulo'] }}
        </h1>

        <!-- MENSAGEM -->
        <p style="color:#4b5563; font-size:16px; margin-bottom:30px; line-height:1.5;">
            {{ $statusData['mensagem'] }}
        </p>

        <hr style="border:0; border-top:1px solid #e5e7eb; margin-bottom:25px;">

        <p style="color:#9ca3af; font-size:13px;">
            Obrigado por utilizar os nossos serviços.
        </p>

        <!-- CTA -->
        <a href="{{ 'http://localhost:4200' }}"
            style="display:inline-block; margin-top:20px; color:#2563eb;
                      text-decoration:none; font-weight:600; font-size:14px;">
            Voltar ao Portal
        </a>
    </div>

</body>

</html>
