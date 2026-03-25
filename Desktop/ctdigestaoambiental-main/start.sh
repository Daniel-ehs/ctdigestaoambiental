#!/bin/sh
set -e

echo "Starting CTDI EcoTrack Node Server..."
echo "NODE_ENV: $NODE_ENV"
echo "PORT: $PORT"

# Run migrations/db push
echo "Running Prisma DB Push..."
npx prisma db push --accept-data-loss || echo "Prisma push failed, continuing..."

echo "Starting node server.cjs..."
exec node server.cjs
