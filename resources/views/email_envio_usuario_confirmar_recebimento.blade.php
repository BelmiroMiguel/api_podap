<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Recebimento Confirmado') }}</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f7; padding: 20px;">
    <div
        style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e1e1e1;">
        <h2 style="color: #1f2937; text-align: center;">Confirmação de Entrega</h2>

        <p>Olá, <strong>{{ $usuarioReceptor->nome }}</strong>,</p>

        <p>O processo de devolução do item referente à ocorrência <strong>#{{ $ocorrencia->idOcorrencia }}</strong> foi
            iniciado. Por favor, confirme se já tem o objeto em sua posse.</p>

        <div style="background-color: #f9fafb; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;"><strong>Item:</strong> {{ $ocorrencia->item->titulo }}</p>
            <p style="margin: 0;"><strong>Categoria:</strong> {{ $ocorrencia->item->categoria->descricao }}</p>
            <p style="margin: 0;"><strong>Local da possível perda:</strong> {{ $ocorrencia->localEvento }}</p>
            <p style="margin: 0;"><strong>Data aproximada da perda:</strong>
                {{ $ocorrencia->dataEvento->format('d/m/Y') }}
            </p>
            <p style="margin: 0; margin-top: 3px;"><strong> {{ $ocorrencia->item->descricao }}</strong></p>
        </div>

        <p style="text-align: center; margin-top: 30px;">
            <!-- Botão de Confirmação (Já definido no construtor) -->
            <a href="{{ $linkConfirmacao }}"
                style="background-color: #16a34a; color: white; padding: 10px; text-decoration: none;">
                Sim, Recebi o Objeto
            </a>

            <!-- Botão de Negação (Gerado dinamicamente) -->
            <a href="{{ $linkNegacao }}"
                style="background-color: #dc2626; color: white; padding: 10px; text-decoration: none;">
                Não Recebi
            </a>

        </p>

        <p style="font-size: 12px; color: #6b7280; text-align: center; margin-top: 40px;">
            Este link é válido por <strong>48 horas</strong>. Se você não reconhece esta ação, clique em "Não Recebi" ou
            ignore este e-mail.
        </p>
    </div>
</body>

</html>
