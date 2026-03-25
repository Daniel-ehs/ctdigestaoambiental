# Build Stage
FROM node:20-alpine AS builder

# Install openssl for Prisma
RUN apk -U add openssl

ENV NODE_ENV=production
ENV PORT=3000

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
FROM node:20-alpine

RUN apk -U add openssl

ENV NODE_ENV=production
ENV PORT=3000

WORKDIR /app

COPY package*.json ./

# Install only production docs (and typescript/ts-node if needed for seed, typically we compile seed or use ts-node in prod which is fine for small apps)
# We need prisma cli for migrations in prod startup often, or we use a start script.
RUN npm install --production
RUN npm install -D typescript ts-node prisma

COPY . .
RUN chmod +x start.sh

EXPOSE 3000

CMD ["./start.sh"]
