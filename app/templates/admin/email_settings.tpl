{extends file="layout/main.tpl"}
{block name="content"}
<h1>Email Settings (Gmail SMTP)</h1>

{if $success}
    <div class="alert alert-success">{$success|escape}</div>
{/if}
{if $error}
    <div class="alert alert-error">{$error|escape}</div>
{/if}

<p style="color:#666; margin-bottom:20px;">Configure Gmail SMTP to send approval and rejection emails when timesheets are approved or rejected. Uses Gmail App Password (not your regular password).</p>

<form method="post" action="/admin/settings/email" style="max-width: 600px;">
    <input type="hidden" name="{$csrf}" value="{$hash}">
    <div class="form-group">
        <label for="SMTPHost">SMTP Host <span style="color:#991b1b;">*</span></label>
        <input type="text" name="SMTPHost" id="SMTPHost" value="{$config.SMTPHost|default:'smtp.gmail.com'|escape}" required placeholder="smtp.gmail.com" style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group">
        <label for="SMTPUser">SMTP Username (Gmail address) <span style="color:#991b1b;">*</span></label>
        <input type="email" name="SMTPUser" id="SMTPUser" value="{$config.SMTPUser|default:''|escape}" required placeholder="yourname@gmail.com" style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group">
        <label for="SMTPPass">SMTP Password (App Password) <span style="color:#991b1b;">*</span></label>
        <input type="password" name="SMTPPass" id="SMTPPass" value="" placeholder="{if $config.SMTPPass}(configured){else}Leave blank to keep current{/if}" style="width:100%; padding: 8px 12px;">
        <p style="color:#666; font-size:0.85em; margin-top:4px;">Use a Gmail App Password. Enable 2FA, then create at <a href="https://myaccount.google.com/apppasswords" target="_blank">myaccount.google.com/apppasswords</a></p>
    </div>
    <div class="form-group">
        <label for="SMTPPort">SMTP Port</label>
        <select name="SMTPPort" id="SMTPPort" style="padding: 8px 12px;">
            <option value="587" {if ($config.SMTPPort|default:587) == 587}selected{/if}>587 (TLS)</option>
            <option value="465" {if ($config.SMTPPort|default:587) == 465}selected{/if}>465 (SSL)</option>
        </select>
    </div>
    <div class="form-group">
        <label for="SMTPCrypto">Encryption</label>
        <select name="SMTPCrypto" id="SMTPCrypto" style="padding: 8px 12px;">
            <option value="tls" {if ($config.SMTPCrypto|default:'tls') == 'tls'}selected{/if}>TLS (port 587)</option>
            <option value="ssl" {if ($config.SMTPCrypto|default:'tls') == 'ssl'}selected{/if}>SSL (port 465)</option>
        </select>
    </div>
    <div class="form-group">
        <label for="fromEmail">From Email</label>
        <input type="email" name="fromEmail" id="fromEmail" value="{$config.fromEmail|default:''|escape}" placeholder="Defaults to SMTP username" style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group">
        <label for="fromName">From Name</label>
        <input type="text" name="fromName" id="fromName" value="{$config.fromName|default:'RTMS'|escape}" style="width:100%; padding: 8px 12px;">
    </div>
    <div class="form-group" style="margin-top: 24px;">
        <label style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" name="test" value="1"> Send test email after saving
        </label>
    </div>
    <p style="margin-top: 24px;">
        <button type="submit" class="btn">Save &amp; Validate</button>
        <a href="/admin/products/manage" class="btn btn-secondary">Cancel</a>
    </p>
</form>

<p style="margin-top: 32px; color:#666; font-size:0.9em;">
    <strong>Where emails are sent:</strong> When a Manager or Product Lead approves or rejects a timesheet entry on the <a href="/approval">Approval</a> page, the employee receives an email at their registered address.
</p>
{/block}
