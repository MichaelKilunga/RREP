---
paths:
  - 'app/**'
---

# App

## SmsService: pass manual: true for staff-triggered SMS
SmsService::send()/deliver() skip automatic notifications when the "Push SMS notifications" feature toggle (feature_sms_notifications_enabled) is off; the sms_enabled master switch blocks everything. Any SMS a staff member sends on purpose (customer SMS, invoice reminder, payment receipt, SMS center, resend) must pass `manual: true`. Use deliver() when the UI must show why a message was not sent ($result['reason']). Every attempt, including Skipped, is logged to communication_logs with a reason; never call the gateway directly or synchronously on page render (the balance loads via AJAX).
