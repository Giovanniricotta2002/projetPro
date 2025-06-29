# JWT Keys Generation and Management

## For Development Environment

Generate JWT keys locally:

```bash
# Generate private key
openssl genrsa -out config/jwt/private.pem 2048

# Generate public key from private key
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

# Optional: Generate with passphrase for extra security
openssl genrsa -aes256 -out config/jwt/private.pem 2048
```

## For Production Environment

1. Generate keys on the production server directly
2. Use environment variables or secrets management
3. Set proper file permissions:

   ```bash
   chmod 600 config/jwt/private.pem
   chmod 644 config/jwt/public.pem
   ```

## Security Notes

- Private key MUST remain secret
- Public key can be shared with services that need to validate tokens
- Never commit real keys to version control
- Use strong passphrases in production
- Rotate keys regularly (especially if compromised)

## Testing Keys

The current keys in this project are placeholder keys for development only.
Replace them with proper keys before deploying to production.
