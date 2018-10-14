<?php

/**
 * User Entity
 */

namespace AppBundle\Entity;

use AppBundle\Entity\Token;
use AppBundle\Entity\UserImage;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Validator\NoSql;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User
 *
 * @ORM\Table(name="st_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Cette adresse email est déjà utilisée !")
 * @UniqueEntity(fields="username", message="Ce nom d'utilisateur est déjà utilisé !")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @var int
     * @access private
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @access private
     * @ORM\Column(name="username", type="string", length=30, unique=true)
     * @Assert\NotBlank(message = "Vous devez entrer un nom d'utilisateur")
     * @Assert\Length(
     *      min = 4,
     *      max = 20,
     *      minMessage = "Le nom d'utilisateur doit faire au minimum 4 caractères",
     *      maxMessage = "Le nom d'utilisateur doit faire au maximum 30 caractères"
     * )
     * @NoSql()
     * @Assert\Regex(
     *      pattern = "/^[a-zA-Z0-9-]{4,}$/",
     *      message = "(a-z, A-Z, -, 0-9)"
     * )
     */
    private $username;

    /**
     * @var string
     * @access private
     * @ORM\Column(name="password", type="string", length=64)
     * @Assert\NotBlank(message = "Vous devez entrer un mot de passe")
     * @Assert\Length(
     *      min = 8,
     *      max = 48,
     *      minMessage = "Le mot de passe doit faire au minimum 8 caractères",
     *      maxMessage = "Le mot de passe doit faire au maximum 48 caractères"
     * )
     * @NoSql()
     * @Assert\Regex(
     *      pattern = "/[a-zA-Z]+[0-9]+[a-zA-z0-9_-]{6,}/",
     *      message = "Votre mot de passe doit contenir au moins une lettre et un chiffre, tirets (-, _) autorisé"
     * )
     */
    private $password;

    /**
     * @var string
     * @access private
     * @ORM\Column(name="email", type="string", length=70, unique=true)
     * @Assert\NotBlank(message = "Vous devez entrer une adresse email")
     * @Assert\Length(
     *      min = 6,
     *      max = 70,
     *      minMessage = "L'adresse email doit faire au minimum 6 caractères",
     *      maxMessage = "L'adresse email doit faire au maximum 70 caractères"
     * )
     * @NoSql()
     * @Assert\Email(message = "Merci de saisir une adresse email valide !")
     */
    private $email;

    /**
     * @var string
     * @access private
     * @ORM\Column(name="name", type="string", length=40)
     * @Assert\NotBlank(message = "Vous devez entrer votre nom")
     * @Assert\Length(
     *      min = 2,
     *      max = 40,
     *      minMessage = "Le nom doit faire au minimum 2 caractères",
     *      maxMessage = "Le nom doit faire au maximum 40 caractères"
     * )
     * @NoSql()
     * @Assert\Regex(
     *      pattern = "/^[a-zA-Z]+-?[a-zA-Z]{1,}/",
     *      message = "(a-z, A-Z, -)"
     * )
     */
    private $name;

    /**
     * @var string
     * @access private
     * @ORM\Column(name="firstName", type="string", length=40)
     * @Assert\NotBlank(message = "Vous devez entrer votre prénom")
     * @Assert\Length(
     *      min = 2,
     *      max = 40,
     *      minMessage = "Le prénom doit faire au minimum 2 caractères",
     *      maxMessage = "Le prénom doit faire au maximum 40 caractères"
     * )
     * @NoSql()
     * @Assert\Regex(
     *      pattern = "/^[a-zA-Z]+-?[a-zA-Z]{1,}/",
     *      message = "(a-z, A-Z, -)"
     * )
     */
    private $firstName;

    /**
     * @var bool
     * @access private
     * @ORM\Column(name="isActive", type="boolean")
     */
    private $isActive;

    /**
     * @var array
     * @access private
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /**
     * @var UserImage
     * @access private
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\UserImage", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Valid()
     */
    private $image;

    /**
     * @var Token
     * @access private
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Token", inversedBy="token", cascade={"persist"})
     */
    private $token;

    /**
     * Undocumented function
     */
    public function __construct()
    {
        $this->isActive = false;
    }

    /**
     * Get id
     * @access public
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     * @access public
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     * @access public
     * 
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     * @access public
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     * @access public
     * 
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email
     * @access public
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     * @access public
     * 
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set name
     * @access public
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @access public
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set firstName
     * @access public
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     * @access public
     * 
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set isActive
     * @access public
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function IsEnabled()
    {
        return $this->isActive;
    }

    /**
     * Set roles
     * @access public
     * @param array $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     * @access public
     * 
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set image
     * @access public
     * @param UserImage $image
     * 
     * @return User
     */
    public function setImage(UserImage $image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     * @access public
     * 
     * @return UserImage
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set token
     * @access public
     * @param Token $token
     *
     * @return User
     */
    public function setToken(Token $token = null)
    {
        $this->token = $token;
        $token->setUser($this);

        return $this;
    }

    /**
     * Get token
     * @access public
     * 
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
        ) = unserialize($serialized);
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {

    }
}
