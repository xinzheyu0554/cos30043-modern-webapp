

CREATE TABLE IF NOT EXISTS `Group` (
    groupId     INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category    VARCHAR(100) NOT NULL,
    imageUrl    VARCHAR(500) DEFAULT NULL,
    createdBy   INT NOT NULL,
    createdAt   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (createdBy) REFERENCES `User`(userId)
);

CREATE TABLE IF NOT EXISTS `Membership` (
    membershipId INT AUTO_INCREMENT PRIMARY KEY,
    groupId      INT NOT NULL,
    userId       INT NOT NULL,
    joinedAt     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (groupId) REFERENCES `Group`(groupId),
    FOREIGN KEY (userId) REFERENCES `User`(userId),
    UNIQUE KEY unique_membership (groupId, userId)
);


INSERT INTO `Group` (name, description, category, imageUrl, createdBy, createdAt) VALUES
('Women in STEM',
 'Hi from the Women in STEM Club Swinburne! 💕 We are all about community, empowerment, and supporting each other through our STEM journeys. Scroll down to discover our events, merch, and how you can become a member! We would love to have you! 🎉',
 'Academic',
 '/groups/wistem.png',
 1, '2026-05-15 11:15:00'),

('Swinburne KPOP Club',
 'Our club brings together Kpop fans from across Swinburne to create a fun and inclusive community.',
 'Social',
 '/groups/kpopswinburne.png',
 1, '2026-05-15 14:22:00'),

('F1 Club',
 'At UniMelb, whether you are a rookie or a champion, our club is the pole position for all! Welcome to the Formula 1 community where we stream F1 qualifying and race sessions, every week, no matter the time and no matter the location. We are also hosting F1 Fantasy League and Trivia Night competitions where you can win prizes. Whether you are an F1 fan or not, come along and connect with like-minded people to foster long-lasting friendships. LIGHTS OUT AND AWAY WE GO!.',
 'Social',
 '/groups/f1.png',
 2, '2026-05-15 14:25:00'),

('Games Makers Club',
 'The UniMelb Game Makers Club is open to everyone interested in making games regardless of experience! We host game-making competitions (game jams), meetups, social events, career events, workshops, and a discord server.',
 'Social',
 '/groups/gamesmakers.png',
 2, '2026-05-15 14:27:00'),

('Swinburne Sustainability Society',
 'Swinburne Sustainability Society is here to bring you sustainability related events on and off campus!Want to be more sustainable everyday? Find it hard to start? Dont know what being green , cruelty-free , or carbon neutral means?',
 'Social',
 '/groups/sustainability.png',
 3, '2026-05-15 14:29:00'),

('Mechatronics Student Association',
 'Hi and Welcome to the Mechatronics Student Association of RMIT! Mechatronics is an engineering discipline that encompasses robotics, electrical, electronics and mechanical engineering systems.',
 'Social',
 '/groups/mechatronics.png',
 1, '2026-05-15 14:40:00'),

('Monash Creative Writers',
 'The home for Monash students who are passionate about all things storytelling!',
 'Social',
 '/groups/writing.png',
 3, '2026-05-15 14:42:00'),

('Pixel Swin',
 'PixelSwin is Swinburne photography club! We foster an engaging, lively, and active community of photographers and enthusiasts alike. All skill and experience levels are welcome!',
 'Social',
 '/groups/pixel.png',
 2, '2026-05-15 14:45:00');