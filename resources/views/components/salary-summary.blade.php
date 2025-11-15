<div style="font-family: monospace; line-height: 1.8; padding: 16px; background-color: #f8f9fa; border-radius: 8px;">
    <div style="margin-bottom: 12px;">
        <span>Gaji Pokok + Tunjangan:</span>
        <strong style="float: right; color: #000;">Rp {{ number_format($base_salary + $allowance, 0, ',', '.') }}</strong>
    </div>

    @if ($additional > 0)
        <div style="margin-bottom: 12px;">
            <span>Tambahan:</span>
            <strong style="float: right; color: #28a745;">+ Rp {{ number_format($additional, 0, ',', '.') }}</strong>
        </div>
    @endif

    @if ($deductions > 0)
        <div style="margin-bottom: 12px;">
            <span>Potongan:</span>
            <strong style="float: right; color: #dc3545;">- Rp {{ number_format($deductions, 0, ',', '.') }}</strong>
        </div>
    @endif

    <hr style="margin: 12px 0; border: none; border-top: 2px solid #dee2e6;">

    <div style="font-size: 1.2em; font-weight: bold;">
        <span>NET SALARY:</span>
        <strong style="float: right; color: #007bff;">Rp {{ number_format($net, 0, ',', '.') }}</strong>
    </div>
    <div style="clear: both;"></div>
</div>
