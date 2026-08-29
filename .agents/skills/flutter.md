# Flutter Architecture Skill

## Principles & Rules
1. Flutter client consumes clean REST APIs under `/api/v1/` and listens to WebSocket events.
2. Zero business logic in Flutter: calculation of grades, fees, attendance percentages, and AI orchestration happens on backend.
3. State management: Clean Provider/Bloc/Riverpod architecture with immutable state models.
4. Offline capability: Cache read models locally using Hive/Isar/SharedPreferences for instant offline rendering.
5. Role-tailored navigators: Smooth dashboard switches for Teachers, Students, Parents (with child switcher), and Admins.
