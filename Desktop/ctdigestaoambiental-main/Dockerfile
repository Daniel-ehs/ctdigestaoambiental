# Build Stage
FROM node:18-alpine AS builder

WORKDIR /app

COPY package*.json ./
COPY prisma ./prisma/

RUN npm install

COPY . .

# Build Vite App
RUN npm run build
# Generate Prisma Client
RUN npx prisma generate

# Production Stage
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./

# Install only production docs (and typescript/ts-node if needed for seed, typically we compile seed or use ts-node in prod which is fine for small apps)
# We need prisma cli for migrations in prod startup often, or we use a start script.
RUN npm install --production
RUN npm install -D typescript ts-node prisma

COPY --from=builder /app/dist ./dist
COPY --from=builder /app/prisma ./prisma
COPY --from=builder /app/node_modules/.prisma ./node_modules/.prisma
COPY . .

EXPOSE 3000

# Start command: Apply migrations (if possible) then start server
# Note: In CapRover, you might want to run migrations manually or in a separate command, 
# but for simplicity we can try to run them on start or just generate client.
# Better to use a start script.
CMD ["npm", "run", "start:prod"]
