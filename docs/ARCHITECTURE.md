# Architecture

## Auth
Laravel Sanctum based auth

## Roles
Spatie Permission package

## API Structure
Controller -> Service -> Repository

## Response Standard
All APIs return:
{
  success,
  message,
  data,
  meta
}